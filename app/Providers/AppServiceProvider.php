<?php

namespace App\Providers;

use App\Application\Auth\CurrentUserResolver;
use App\Domain\Contracts\Repositories\BalanceSnapshotRepositoryInterface;
use App\Domain\Contracts\Repositories\GoalRepositoryInterface;
use App\Domain\Contracts\Repositories\IncomeRepositoryInterface;
use App\Domain\Contracts\Repositories\ObligationPaymentRepositoryInterface;
use App\Domain\Contracts\Repositories\ObligationRepositoryInterface;
use App\Domain\Contracts\Repositories\SavingRepositoryInterface;
use App\Domain\Contracts\Repositories\UserRepositoryInterface;
use App\Domain\Contracts\Repositories\UserSettingsRepositoryInterface;
use App\Infrastructure\Persistence\Repositories\EloquentBalanceSnapshotRepository;
use App\Infrastructure\Persistence\Repositories\EloquentGoalRepository;
use App\Infrastructure\Persistence\Repositories\EloquentIncomeRepository;
use App\Infrastructure\Persistence\Repositories\EloquentObligationPaymentRepository;
use App\Infrastructure\Persistence\Repositories\EloquentObligationRepository;
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
        ObligationRepositoryInterface::class => EloquentObligationRepository::class,
        ObligationPaymentRepositoryInterface::class => EloquentObligationPaymentRepository::class,
        IncomeRepositoryInterface::class => EloquentIncomeRepository::class,
        GoalRepositoryInterface::class => EloquentGoalRepository::class,
        SavingRepositoryInterface::class => EloquentSavingRepository::class,
        BalanceSnapshotRepositoryInterface::class => EloquentBalanceSnapshotRepository::class,
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
