# Backend — статус к prod MVP v1

Краткая фиксация по коду `kopeika-back` (на фоне плана soft-launch).  
Подробный продуктовый обзор — в корневом `kopeika/docs/STATUS.md` и Agent Store `docs/mvp-readiness.md` / `docs/prod-mvp-v1-plan.md`.

## Реализовано (API)

| Область | Эндпоинты / механизм |
|---------|----------------------|
| Health | `GET /api/health` |
| Auth | register / login / logout / user, forgot + reset password (Sanctum cookie SPA) |
| Dashboard | `GET /api/dashboard` — лимит по **доходам-якорям**, зоны, прогноз (+ timeline), goals, streak, check-in due |
| Settings | `GET/PUT /api/settings` — `notification_mode`, `buffer_amount`, streak / last check-in |
| Obligations + payments | CRUD, detail summary, история / внесение / удаление / close / reopen |
| Incomes | CRUD; recurring + `day_of_month`; spending anchors для safe-to-spend |
| Savings | CRUD (+ сводка на dashboard) |
| Goals | CRUD (+ планировщик отложений на dashboard) |
| Balance | `POST /api/balance` |
| Calendar | `GET /api/calendar` — платежи **и** доходы |
| Check-in | `POST /api/check-in` (+ QuickExpense при крупной трате; streak в `CheckInService`) |
| Push subscriptions | `POST/DELETE /api/push-subscriptions` |
| Reminders job | `notifications:send-reminders` по расписанию `dailyAt('09:00')` |

Формат ответа: без обёртки `data` (список = массив, объект = корень).

## Эволюция модели (не путать со старыми доками)

- **Якоря лимита** — recurring incomes с `day_of_month` (`IncomeAnchorBuilder`), не `salary_day_of_month` в settings.
- **Импорт выписок / reconciliation** — убраны из кода и миграциями (таблицы import/transactions/summaries удалены). Не возвращать в scope v1.
- Старые утверждения про `salary_day`, `POST /api/imports`, dual-anchor salary/import — **устарели**.

## Сознательно вне scope v1

- Админка, эквайринг, grocery / WB–Ozon.
- Учёт savings/goals в формуле safe-to-spend (сейчас только отображение / планировщик).
- Зоны yellow/red в push (job шлёт check-in + платежи ≤3 дня).
- User-facing поиск через Meilisearch/Scout.

## Нужно для публичного soft-launch (бэк + инфра)

| ID | Что | Сейчас |
|----|-----|--------|
| BE-1 | Реальный Web Push (VAPID) | `SendRemindersCommand::dispatchPush` только `Log::info` |
| BE-2 | Реальный mailer для reset password | `.env.example`: `MAIL_MAILER=log` |
| BE-3 | Контракт подписок под lib | Store/destroy есть; сверить encoding/keys с web-push |
| INFRA | HTTPS cookies, CORS/Sanctum domains, `queue:work` + `schedule:run`, Telescope не публичен | локально через Sail ок |

После mail + real push + prod env/queue/scheduler бэк готов сопровождать soft-launch (PWA — сторона фронта).

## Тесты

Feature: Auth, Dashboard, Obligations, Payments.  
Unit: SafeToSpend, DebtPayoff, ObligationSchedule/Progress, Auth.  
Нет feature-тестов incomes / savings / calendar / check-in / goals / push — желательно точечно перед раздачей, не блокер core loop.
