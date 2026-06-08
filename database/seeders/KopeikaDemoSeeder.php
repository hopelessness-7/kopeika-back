<?php

namespace Database\Seeders;

use App\Domain\Contracts\Repositories\BalanceSnapshotRepositoryInterface;
use App\Domain\Contracts\Repositories\IncomeRepositoryInterface;
use App\Domain\Contracts\Repositories\ObligationPaymentRepositoryInterface;
use App\Domain\Contracts\Repositories\ObligationRepositoryInterface;
use App\Domain\Contracts\Repositories\ReconciliationSettingsRepositoryInterface;
use App\Domain\Contracts\Repositories\UserRepositoryInterface;
use App\Domain\Contracts\Repositories\UserSettingsRepositoryInterface;
use App\Domain\DemoUser;
use App\Domain\Enums\BalanceSnapshotSource;
use App\Domain\Enums\ImportIntervalDays;
use App\Domain\Enums\NotificationMode;
use App\Domain\Enums\ObligationPaymentStatus;
use App\Domain\Enums\ObligationType;
use App\Domain\Enums\PrimaryAnchor;
use App\DTO\Balance\BalanceSnapshotData;
use App\DTO\Income\IncomeData;
use App\DTO\Obligation\ObligationData;
use App\DTO\Obligation\ObligationPaymentData;
use App\DTO\Reconciliation\ReconciliationSettingsData;
use App\DTO\Saving\SavingData;
use App\DTO\Settings\UserSettingsData;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class KopeikaDemoSeeder extends Seeder
{
    public function run(): void
    {
        $userRepository = app(UserRepositoryInterface::class);
        $settingsRepository = app(UserSettingsRepositoryInterface::class);
        $reconciliationRepository = app(ReconciliationSettingsRepositoryInterface::class);
        $obligationRepository = app(ObligationRepositoryInterface::class);
        $incomeRepository = app(IncomeRepositoryInterface::class);
        $balanceRepository = app(BalanceSnapshotRepositoryInterface::class);
        $paymentRepository = app(ObligationPaymentRepositoryInterface::class);

        $user = $userRepository->findByEmail(DemoUser::EMAIL) ?? $this->createDemoUser();

        $settingsRepository->upsert(new UserSettingsData(
            userId: $user->id,
            lastCheckInAt: now()->subDays(3),
            notificationMode: NotificationMode::Normal,
        ));

        $reconciliationRepository->upsert(new ReconciliationSettingsData(
            userId: $user->id,
            importIntervalDays: ImportIntervalDays::TenDays,
            lastImportAt: now()->subDays(6),
            primaryAnchor: PrimaryAnchor::Auto,
            salaryDayOfMonth: 25,
        ));

        if ($incomeRepository->listForUser($user->id)->isNotEmpty()) {
            return;
        }

        $mortgage = $obligationRepository->create(new ObligationData(
            userId: $user->id,
            title: 'Ипотека',
            type: ObligationType::Loan,
            paymentAmount: '24100.00',
            paymentDay: 5,
            remainingAmount: '2500000.00',
            totalAmount: '3000000.00',
            interestRate: '12.00',
        ));

        $obligationRepository->create(new ObligationData(
            userId: $user->id,
            title: 'Аренда',
            type: ObligationType::Rent,
            paymentAmount: '35000.00',
            paymentDay: 1,
        ));

        $obligationRepository->create(new ObligationData(
            userId: $user->id,
            title: 'Подписка Яндекс Плюс',
            type: ObligationType::Subscription,
            paymentAmount: '399.00',
            paymentDay: 15,
        ));

        $personalDebt = $obligationRepository->create(new ObligationData(
            userId: $user->id,
            title: 'Долг другу',
            type: ObligationType::PersonalDebt,
            paymentAmount: '5000.00',
            paymentDay: 20,
            remainingAmount: '15000.00',
            totalAmount: '30000.00',
            lender: 'Сергей',
        ));

        foreach ([2, 1] as $monthsAgo) {
            $paymentRepository->create(new ObligationPaymentData(
                userId: $user->id,
                obligationId: $mortgage->id,
                amount: '24100.00',
                dueDate: now()->subMonthsNoOverflow($monthsAgo)->day(5),
                status: ObligationPaymentStatus::Paid,
                paidAt: now()->subMonthsNoOverflow($monthsAgo)->day(5),
            ));
        }

        foreach ([3, 2, 1] as $monthsAgo) {
            $paymentRepository->create(new ObligationPaymentData(
                userId: $user->id,
                obligationId: $personalDebt->id,
                amount: '5000.00',
                dueDate: now()->subMonthsNoOverflow($monthsAgo)->day(20),
                status: ObligationPaymentStatus::Paid,
                paidAt: now()->subMonthsNoOverflow($monthsAgo)->day(20),
            ));
        }

        $incomeRepository->create(new IncomeData(
            userId: $user->id,
            title: 'Зарплата',
            amount: '120000.00',
            receivedAt: now()->subDays(10),
            description: 'Основной доход',
            isRecurring: true,
            dayOfMonth: 25,
        ));

        $incomeRepository->create(new IncomeData(
            userId: $user->id,
            title: 'Аванс',
            amount: '40000.00',
            receivedAt: now()->subDays(20),
            isRecurring: true,
            dayOfMonth: 10,
        ));

        $incomeRepository->create(new IncomeData(
            userId: $user->id,
            title: 'Подработка',
            amount: '12000.00',
            receivedAt: now()->subDays(4),
            description: 'Разовый проект',
        ));

        app(\App\Domain\Contracts\Repositories\SavingRepositoryInterface::class)->create(new SavingData(
            userId: $user->id,
            title: 'Подушка безопасности',
            bank: 'Сбер',
            balance: '150000.00',
            monthlyContribution: '10000.00',
        ));

        $balanceRepository->record(new BalanceSnapshotData(
            userId: $user->id,
            amount: '21800.00',
            source: BalanceSnapshotSource::Manual,
            recordedAt: now(),
            note: 'Баланс на счёте',
        ));
    }

    private function createDemoUser(): User
    {
        return User::query()->create([
            'name' => DemoUser::NAME,
            'email' => DemoUser::EMAIL,
            'password' => Hash::make(DemoUser::PASSWORD),
        ]);
    }
}
