# Архитектура kopeika-back (DDD-lite)

```
app/
├── Domain/
│   ├── Enums/
│   └── Contracts/Repositories/
├── DTO/                       # один класс на сущность
│   ├── Contracts/DataTransferObject.php
│   ├── Concerns/MapsFromArray.php
│   ├── Obligation/ObligationData.php
│   ├── Income/IncomeData.php
│   ├── Settings/UserSettingsData.php
│   └── …
├── Application/
│   ├── Finance/               # SafeToSpend, IncomeAnchorBuilder, forecast, schedule…
│   ├── Services/              # Dashboard, Obligation, CheckIn, Push, …
│   ├── Auth/
│   └── Support/
├── Models/
├── Infrastructure/
│   └── Persistence/Repositories/
├── Http/
│   ├── Controllers/Api/
│   ├── Requests/              # BaseRequest::getDto() → тот же DTO
│   └── Resources/
├── Console/Commands/          # notifications:send-reminders
└── Notifications/             # ResetPasswordNotification (queued)
```

## Поток данных

1. **HTTP** → Form Request (валидация) → `$request->getDto()` → `*Data`
2. **Controller** → `*Service` (Application)
3. **Repository** (контракт Domain → Eloquent Infrastructure) → Model → БД
4. Ответ: Resource или массив из сервиса (без обёртки `data`)

Один и тот же `*Data` используется в Form Request, сервисе, репозитории и сидере. Отдельных `Store*Data` / `Update*Data` нет.

## Финансы (важное)

- Safe-to-spend строится по **доходам-якорям** (`Income` + `day_of_month`), не по полю `salary_day` в settings.
- `buffer_amount` из `user_settings` участвует в формуле.
- Накопления (`savings`) и цели (`goals`) отдаются на dashboard, но **не** входят в daily limit (осознанно).
- Импорт банковских выписок / reconciliation **удалены** из домена.

## Соглашения

| Тема | Правило |
|------|---------|
| DTO | `App\DTO\{Entity}\{Entity}Data`, implements `DataTransferObject` |
| Сборка | `fromArray()` — из `validated()` или вручную в сидере |
| В модель | `toModelAttributes()`; для update — `toModelAttributes(forUpdate: true)` где нужно |
| Form Request | разные классы с разными `rules()`, один `dtoClass()` |
| Enum'ы | `App\Domain\Enums\*` |

См. также [MVP.md](./MVP.md).
