<?php

namespace App\DTO\Reconciliation;

use App\Domain\Enums\ImportIntervalDays;
use App\Domain\Enums\PrimaryAnchor;
use App\DTO\Concerns\ArrayAccessible;
use App\DTO\Concerns\MapsFromArray;
use App\DTO\Contracts\DataTransferObject;
use App\Models\ReconciliationSetting;
use Carbon\CarbonInterface;

readonly class ReconciliationSettingsData implements DataTransferObject
{
    use ArrayAccessible;
    use MapsFromArray;

    public function __construct(
        public int $userId,
        public ImportIntervalDays $importIntervalDays,
        public ?CarbonInterface $lastImportAt,
        public PrimaryAnchor $primaryAnchor,
        public ?int $salaryDayOfMonth,
    ) {}

    public static function fromArray(array $data): static
    {
        $interval = isset($data['import_interval_days'])
            ? ImportIntervalDays::fromDays((int) $data['import_interval_days'])
            : ImportIntervalDays::TenDays;

        return new self(
            userId: (int) $data['user_id'],
            importIntervalDays: $interval,
            lastImportAt: self::carbon($data, 'last_import_at'),
            primaryAnchor: self::enum($data, 'primary_anchor', PrimaryAnchor::class, PrimaryAnchor::Auto),
            salaryDayOfMonth: self::int($data, 'salary_day_of_month'),
        );
    }

    public function toModelAttributes(): array
    {
        return [
            'user_id' => $this->userId,
            'import_interval_days' => $this->importIntervalDays->value,
            'last_import_at' => $this->lastImportAt,
            'primary_anchor' => $this->primaryAnchor,
            'salary_day_of_month' => $this->salaryDayOfMonth,
        ];
    }
}
