# Contributing

Спасибо за интерес к **kopeika-back**. Ниже — минимальный порядок работы.

## Быстрый старт

См. [README.md](README.md): Docker-сеть `kopeika`, Sail, миграции.

```bash
docker network create kopeika 2>/dev/null || true
cp .env.example .env
./vendor/bin/sail up -d --build
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan test
```

## Ветки и PR

1. Форк или ветка от актуального `main`.
2. Одна тема = один PR (фича / фикс / docs / ci).
3. Сообщения коммитов: conventional style, например `feat(api): …`, `fix: …`, `docs: …`, `ci: …`.
4. Перед PR: `./vendor/bin/sail artisan test` (или `php artisan test` с локальным PHP 8.4+).
5. CI на GitHub Actions должен быть зелёным.

## Стиль кода

- PHP 8.4+ / Laravel 13.
- Слои: `Domain` → `DTO` → `Application` → `Infrastructure` → `Http` (см. [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md)).
- Не коммить `.env`, ключи VAPID private, секреты.

## Вопросы

Открывай GitHub Discussion/Issue с воспроизведением и ожидаемым поведением. Уязвимости — только по [SECURITY.md](SECURITY.md).
