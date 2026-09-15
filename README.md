# kopeika-back

Бэкенд Kopeika — Laravel 13 + Sail + nginx + PHP-FPM 8.5 + MariaDB 11 + Redis + Meilisearch + Telescope + Scout.

## Стек

| Сервис        | Порт (host) | Внутри (контейнер) |
|---------------|-------------|--------------------|
| nginx         | `8080`      | `80`               |
| laravel.test  | —           | `9000` (PHP-FPM)   |
| mariadb       | `3306`      | `3306`             |
| redis         | `6379`      | `6379`             |
| meilisearch   | `7700`      | `7700`             |

Все сервисы — в общей внешней docker-сети **`kopeika`**.

## Первый запуск

```bash
# 1. Общая сеть (если ещё не создана; общая для kopeika-back и kopeika-front)
docker network create kopeika 2>/dev/null || true

# 2. .env
cp .env.example .env

# 3. Поднять стек (Sail + наш собственный nginx + Compose)
./vendor/bin/sail up -d --build

# 4. Ключ + миграции + Telescope
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
```

Открыть:

- API: http://localhost:8080/api/health
- Telescope: http://localhost:8080/telescope
- Meilisearch: http://localhost:7700

## Команды

```bash
./vendor/bin/sail up -d
./vendor/bin/sail down
./vendor/bin/sail artisan ...
./vendor/bin/sail composer ...
./vendor/bin/sail tinker
./vendor/bin/sail logs -f nginx
```

Расписание (нужен `schedule:run` на проде):

```bash
./vendor/bin/sail artisan notifications:send-reminders   # вручную
# Schedule::command(...)->dailyAt('09:00') — см. routes/console.php
```

## Структура docker

```
docker/
├── 8.5/                     # PHP-FPM runtime (опубликованный Sail, кастомизирован под FPM)
│   ├── Dockerfile
│   ├── php.ini
│   ├── start-container
│   ├── supervisord.conf      # запускает php-fpm8.5
│   └── zz-kopeika.conf       # FPM pool: listen 0.0.0.0:9000
├── nginx/
│   └── default.conf          # fastcgi → laravel.test:9000
└── mariadb/
    └── create-testing-database.sh
```

## Telescope

Доступен на `/telescope`. В `local` окружении — без авторизации (см. `app/Providers/TelescopeServiceProvider.php`). Для prod — выключить или жёстко ограничить allow-list.

## Scout / Meilisearch

`SCOUT_DRIVER=meilisearch`, `MEILISEARCH_HOST=http://meilisearch:7700`. Индексация — `php artisan scout:import "App\\Models\\..."`.  
User-facing поиск по сущностям в продукте пока не используется.

## Архитектура

Слои DDD-lite: `Domain` → `DTO` → `Application` (Finance + Services) → `Infrastructure` → `Http`.

- Подробнее: [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md)
- Готовность к prod MVP v1 (бэк): [docs/MVP.md](docs/MVP.md)

## Связанные документы

В корневой `kopeika/docs/` (общая директория проекта, **вне git** бэка/фронта):

- `STATUS.md` — актуальный статус продукта / API
- `api-status.md` — таблица эндпоинтов
- `phase-0-spec.md` — **историческая** спецификация (частично устарела: import / salary_day)
- `frontend-handoff.md` — handoff для фронта (с пометкой устаревших разделов про импорт)
