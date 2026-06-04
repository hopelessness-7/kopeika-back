<?php

namespace App\DTO\Balance;

use App\Domain\Enums\BalanceSnapshotSource;
use App\DTO\Concerns\ArrayAccessible;
use App\DTO\Concerns\MapsFromArray;
use App\DTO\Contracts\DataTransferObject;
use Carbon\CarbonInterface;

readonly class BalanceSnapshotData implements DataTransferObject
{
    use ArrayAccessible;
    use MapsFromArray;

    public function __construct(
        public int $userId,
        public string $amount,
        public BalanceSnapshotSource $source,
        public CarbonInterface $recordedAt,
        public ?int $bankImportId = null,
        public ?string $note = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            userId: (int) $data['user_id'],
            amount: (string) $data['amount'],
            source: self::enum($data, 'source', BalanceSnapshotSource::class, BalanceSnapshotSource::Manual),
            recordedAt: self::carbon($data, 'recorded_at') ?? now(),
            bankImportId: self::int($data, 'bank_import_id'),
            note: self::string($data, 'note'),
        );
    }

    public function toModelAttributes(): array
    {
        return [
            'user_id' => $this->userId,
            'bank_import_id' => $this->bankImportId,
            'amount' => $this->amount,
            'source' => $this->source,
            'recorded_at' => $this->recordedAt,
            'note' => $this->note,
        ];
    }
}
