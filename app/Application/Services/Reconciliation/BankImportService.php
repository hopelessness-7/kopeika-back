<?php

namespace App\Application\Services\Reconciliation;

use App\Application\Auth\CurrentUserResolver;
use App\Application\Services\BaseService;
use App\Application\Support\Money;
use App\Domain\Contracts\Repositories\BankImportRepositoryInterface;
use App\Domain\Enums\BankImportStatus;
use App\Domain\Enums\BankProvider;
use App\Models\BankImport;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class BankImportService extends BaseService
{
    public function __construct(
        CurrentUserResolver $currentUser,
        private readonly BankImportRepositoryInterface $imports,
    ) {
        parent::__construct($currentUser);
    }

    public function list(?int $userId = null): array
    {
        return $this->imports
            ->listForUser($userId ?? $this->currentUserId())
            ->map(fn (BankImport $import) => $this->listItem($import))
            ->all();
    }

    public function show(int $id, ?int $userId = null): array
    {
        $import = $this->imports->findForUserOrFail($userId ?? $this->currentUserId(), $id);
        $import->load(['transactions', 'spendPeriodSummary']);

        return $this->detail($import);
    }

    public function store(UploadedFile $file, string $bank, ?int $userId = null): array
    {
        $userId ??= $this->currentUserId();
        $provider = BankProvider::from($bank);

        $import = new BankImport([
            'user_id' => $userId,
            'bank' => $provider,
            'status' => BankImportStatus::Completed,
            'original_filename' => $file->getClientOriginalName(),
            'file_hash' => hash_file('sha256', $file->getRealPath()),
            'file_size' => $file->getSize(),
            'imported_at' => now(),
        ]);

        $this->imports->save($import);

        $path = sprintf('reconciliation/%d/%d_%s', $userId, $import->id, Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)).'.'.$file->getClientOriginalExtension());
        Storage::disk('local')->putFileAs(
            dirname($path),
            $file,
            basename($path),
        );

        $import->storage_path = $path;
        $this->imports->save($import);

        $this->reconciliationSettingsTouch($userId);

        return $this->detail($import->fresh(['transactions', 'spendPeriodSummary']));
    }

    public function download(int $id, ?int $userId = null): StreamedResponse
    {
        $import = $this->imports->findForUserOrFail($userId ?? $this->currentUserId(), $id);

        if ($import->storage_path === null || ! Storage::disk('local')->exists($import->storage_path)) {
            abort(404, 'Файл выписки не найден.');
        }

        return Storage::disk('local')->download(
            $import->storage_path,
            $import->original_filename ?? 'statement.csv',
        );
    }

    private function reconciliationSettingsTouch(int $userId): void
    {
        $settings = app(\App\Domain\Contracts\Repositories\ReconciliationSettingsRepositoryInterface::class)
            ->findOrCreateForUser($userId);
        $settings->last_import_at = now();
        $settings->save();
    }

    private function listItem(BankImport $import): array
    {
        return [
            'id' => $import->id,
            'bank' => $import->bank->value,
            'status' => $import->status->value,
            'period_from' => $import->period_from?->toDateString(),
            'period_to' => $import->period_to?->toDateString(),
            'original_filename' => $import->original_filename,
            'file_size' => $import->file_size,
            'imported_at' => $import->imported_at?->toIso8601String(),
            'confirmed_at' => $import->confirmed_at?->toIso8601String(),
        ];
    }

    private function detail(BankImport $import): array
    {
        $summary = $import->spendPeriodSummary;

        return [
            ...$this->listItem($import),
            'error_message' => $import->error_message,
            'confirmed_balance' => $import->confirmed_balance !== null
                ? Money::toApiNumber((string) $import->confirmed_balance)
                : null,
            'transactions_count' => $import->transactions->count(),
            'summary' => $summary !== null ? [
                'actual_spend' => Money::toApiNumber((string) $summary->actual_spend),
                'planned_spend' => Money::toApiNumber((string) $summary->planned_spend),
                'delta' => Money::toApiNumber((string) $summary->delta),
            ] : null,
            'download_url' => url("/api/reconciliation/imports/{$import->id}/download"),
        ];
    }
}
