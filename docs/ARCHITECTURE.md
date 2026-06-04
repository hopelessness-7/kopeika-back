# Архитектура kopeika-back (DDD-lite)

```
app/
├── Domain/
├── DTO/                       # один класс на сущность
│   ├── Contracts/DataTransferObject.php
│   ├── Concerns/MapsFromArray.php
│   ├── Obligation/ObligationData.php
│   ├── Settings/UserSettingsData.php
│   └── Balance/BalanceSnapshotData.php
├── Application/
├── Models/
├── Infrastructure/
└── Http/
    └── Requests/BaseRequest.php   # getDto() → тот же DTO
```

## Поток данных

1. **HTTP** → `StoreObligationRequest` (валидация) → `$request->getDto()` → `ObligationData`
2. **Controller** → `ObligationService::store(ObligationData $data)`
3. **Repository** → `create(ObligationData $data)`
4. **Model** → БД

Один и тот же `ObligationData` используется в Form Request, сервисе, репозитории и сидере. Отдельных `Store*Data` / `Update*Data` / `*RequestData` нет.

## Соглашения

| Тема | Правило |
|------|---------|
| DTO | `App\DTO\{Entity}\{Entity}Data`, implements `DataTransferObject` |
| Сборка | `fromArray()` — из `validated()` или вручную в сидере |
| В модель | `toModelAttributes()`; для update — `toModelAttributes(forUpdate: true)` где нужно |
| Form Request | разные классы с разными `rules()`, один `dtoClass()` |
| Enum'ы | `App\Domain\Enums\*` |
