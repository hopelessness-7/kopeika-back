# Security Policy

## Поддерживаемые версии

Актуальная ветка `main` — единственная, для которой принимаются security-фиксы.

## Как сообщить об уязвимости

**Не создавай публичный GitHub Issue** с деталями эксплойта или утечками секретов.

Предпочтительно:

1. [GitHub Security Advisories](https://github.com/hopelessness-7/kopeika-back/security/advisories/new) для этого репозитория, или
2. Email: [hopelessness1808@gmail.com](mailto:hopelessness1808@gmail.com) с темой `[kopeika-back security]`.

Опиши затронутый endpoint/компонент, шаги воспроизведения и влияние. Ответим по возможности быстро; после фикса можем попросить проверить патч.

## Что не считается уязвимостью этого репо

- Локальный Telescope без auth в `APP_ENV=local` (ожидаемо для разработки).
- Отсутствие prod hardening (HTTPS, SMTP, systemd) — это зона деплоя, см. [docs/MVP.md](docs/MVP.md).
