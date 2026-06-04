<?php

namespace App\Providers;

use App\Application\Auth\CurrentUserResolver;
use App\Domain\Contracts\Repositories\BalanceSnapshotRepositoryInterface;
use App\Domain\Contracts\Repositories\BankImportRepositoryInterface;
use App\Domain\Contracts\Repositories\IncomeRepositoryInterface;
use App\Domain\Contracts\Repositories\ObligationRepositoryInterface;
use App\Domain\Contracts\Repositories\ReconciliationSettingsRepositoryInterface;
use App\Domain\Contracts\Repositories\SavingRepositoryInterface;
use App\Domain\Contracts\Repositories\UserRepositoryInterface;
use App\Domain\Contracts\Repositories\UserSettingsRepositoryInterface;
use App\Infrastructure\Persistence\Repositories\EloquentBalanceSnapshotRepository;
use App\Infrastructure\Persistence\Repositories\EloquentBankImportRepository;
use App\Infrastructure\Persistence\Repositories\EloquentIncomeRepository;
use App\Infrastructure\Persistence\Repositories\EloquentObligationRepository;
use App\Infrastructure\Persistence\Repositories\EloquentReconciliationSettingsRepository;
use App\Infrastructure\Persistence\Repositories\EloquentSavingRepository;
use App\Infrastructure\Persistence\Repositories\EloquentUserRepository;
use App\Infrastructure\Persistence\Repositories\EloquentUserSettingsRepository;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public array $bindings = [
        UserRepositoryInterface::class => EloquentUserRepository::class,
        UserSettingsRepositoryInterface::class => EloquentUserSettingsRepository::class,
        ReconciliationSettingsRepositoryInterface::class => EloquentReconciliationSettingsRepository::class,
        ObligationRepositoryInterface::class => EloquentObligationRepository::class,
        IncomeRepositoryInterface::class => EloquentIncomeRepository::class,
        SavingRepositoryInterface::class => EloquentSavingRepository::class,
        BalanceSnapshotRepositoryInterface::class => EloquentBalanceSnapshotRepository::class,
        BankImportRepositoryInterface::class => EloquentBankImportRepository::class,
    ];

    public function register(): void
    {
        $this->app->singleton(CurrentUserResolver::class);
    }

    public function boot(): void
    {
        JsonResource::withoutWrapping();

        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });
    }
}
