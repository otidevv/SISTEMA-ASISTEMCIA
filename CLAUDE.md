# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Laravel 12 application for "Sistema de Asistencia" (Attendance System) for the CEPRE pre-university program at UNAMAD. It manages applicant registration (postulaciones), student enrollment (inscripciones), biometric attendance for students and teachers, teacher scheduling/payroll, document/PDF generation (constancias, carnets), and real-time monitoring.

A companion Flutter mobile app lives in the sibling working directory `CEPRE_UNAMAD_MOBILE` and consumes this app's `routes/api.php` (Sanctum tokens). When changing API contracts, consider the mobile client.

## Technology Stack

- **Backend**: Laravel 12, PHP 8.2+
- **Database**: SQLite by default (`database/database.sqlite`); MySQL/PostgreSQL configurable via `.env`. Note `doctrine/dbal` is required for runtime column changes.
- **Frontend**: Blade + Tailwind CSS v4 (via `@tailwindcss/vite`), vanilla JS, Vite. There is no SPA framework.
- **Auth**: Custom username/password auth + Laravel Sanctum for API tokens; email verification for postulantes.
- **Real-time**: Laravel Reverb (WebSockets) + Laravel Echo.
- **Key packages**: `maatwebsite/excel` (exports), `barryvdh/laravel-dompdf` + `setasign/fpdf` (PDF), `simplesoftwareio/simple-qrcode`, `yajra/laravel-datatables-oracle`, `spatie/laravel-activitylog` (audit trail), Firebase Cloud Messaging (custom `FcmService`).

## Common Development Commands

```bash
# Full setup
composer install && npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed

# Run everything concurrently (serve + queue:listen + pail logs + vite)
composer run dev

# Run individually
php artisan serve
npm run dev
php artisan queue:listen          # REQUIRED for real-time + notifications (jobs are queued)
php artisan reverb:start          # WebSocket server for real-time attendance
php artisan pail                  # tail application logs

# Build
npm run build

# Tests (note: clears config first, then runs PHPUnit)
composer test
php artisan test --filter=TestName

# Lint / format (Laravel Pint)
./vendor/bin/pint
```

> **Tests**: the suite is essentially empty — only `tests/Unit/ExampleTest.php` and `tests/Feature/ExampleTest.php` ship by default. Don't assume meaningful test coverage exists; verify behavior manually or by writing new tests.

## Architecture Overview

Standard Laravel MVC. The non-obvious parts that require reading multiple files:

### Domain language is Spanish
Models, columns, routes, and relationships are named in Spanish (`Inscripcion`, `Postulacion`, `Ciclo`, `Carrera`, `Aula`, `Turno`, `HorarioDocente`, `RegistroAsistencia`, `nombre`, `apellido_paterno`, `numero_documento`, `fecha_hora`). Match this convention in new code. Note a controller and a model can share a name (e.g. `app/Http/Controllers/RegistroAsistencia.php` vs `app/Models/RegistroAsistencia.php`).

### Custom authentication
The `User` model does **not** use the standard `password` column — it stores `password_hash` and overrides `getAuthPassword()`. The identifier is `username` / `numero_documento`, not necessarily email. Mass-assignable fields and casts are all Spanish-named. Keep this in mind for any auth/login/seeding work.

### Database-driven RBAC (the core access pattern)
Roles and permissions live in the database, not in code:
- `User` ↔ `Role` via the `user_roles` pivot (`usuario_id` / `rol_id`); `Role` ↔ `Permission` via `role_permissions`.
- `PermissionsServiceProvider::boot()` loads every row from the `permissions` table and registers each one's `codigo` as a Laravel **Gate** at runtime. New permissions therefore become available simply by inserting DB rows (see `database/seeders/*PermissionsSeeder.php`).
- Routes are guarded with `->middleware('can:permission.codigo')` (e.g. `can:users.view`, `can:reforzamiento.approve`). API routes use `auth:sanctum,web` so both token and session auth work.
- Blade has custom directives from the same provider: `@permission('code') … @endpermission`, `@role('admin') … @endrole`, `@anyrole([...]) … @endanyrole`.
- `User::hasRole()` applies **synonyms**: `profesor`↔`docente`, `estudiante`↔`postulante`, `padre`↔`apoderado`. A check for one matches the other. Known roles include admin, profesor/docente, estudiante/postulante, padre/apoderado, operador, and "Público General".
- `AuthServiceProvider` also defines convenience gates: `is-admin`, `is-profesor`, `is-estudiante`, `has-role`, `has-permission`.

### Real-time attendance pipeline
Biometric devices push raw events that flow through several stages (do not bypass this chain):
1. Device → `asistencia_eventos` table (raw, `procesado=false`); biometric endpoints in `app/Http/Controllers/Api/BiometricApiController.php`.
2. `php artisan asistencia:procesar-eventos` (scheduled every minute) or `asistencia:daemon` (high-frequency `while(true)` loop, `--sleep=2`, for instant real-time) processes pending events into `RegistroAsistencia`.
3. `ProcessNewAttendanceEvent` job fires the `NuevoRegistroAsistencia` event, broadcast on the `asistencia-channel` Reverb channel; the front-end listens via Echo. Teacher fingerprint marks also push instant notifications.
- `AsistenciaHelper` (`app/Helpers/`) holds the business logic for attendance eligibility (`obtenerEstadoHabilitacion`), exam attendance computation, business-day counting, and cycle statistics.

### Scheduled commands (`app/Console/Kernel.php`)
Requires `php artisan schedule:work` (or cron) in production:
- `asistencia:procesar-eventos` / `asistencia:procesar-docentes` — every minute, `withoutOverlapping`.
- `notification:remind-teachers` — hourly teacher reminders.
- `asistencia:notificar-agenda` (and `--tomorrow`) — daily teacher agenda push at 07:00 / 20:00.
- Other commands: `postulaciones:cambiar-estado` (lifecycle), `TestEmail`, `TestFcm`.

### Controllers, services, jobs
- Web controllers in `app/Http/Controllers/`; API controllers in `app/Http/Controllers/Api/` (most extend `Api/BaseController.php` for uniform JSON responses). Shared logic lives in `app/Http/Controllers/Traits/` (e.g. `HandlesSaturdayRotation`, `ProcessesTeacherSessions`, `TeacherDashboardHelpers`).
- `app/Services/`: `FcmService` (Firebase push), `InstitucionalPdfService`, `PaymentValidationService`.
- `app/Helpers/`: `AsistenciaHelper`, `WhatsAppHelper`.
- Exports in `app/Exports/`, imports in `app/Imports/` (Laravel Excel). Notifications in `app/Notifications/`, mailables in `app/Mail/`.
- "Reforzamiento" (reinforcement courses) is a parallel enrollment/payment subsystem (`InscripcionReforzamiento`, `PagoReforzamiento`, `ReforzamientoAdminController`).

### Configuration notes
- Middleware is configured in `bootstrap/app.php` (Laravel 12 style — no `Http/Kernel.php`): `statefulApi()` enabled, CSRF excluded for `api/*`.
- Defaults from `.env`: SQLite DB, `database` session/queue/cache drivers, `MAIL_MAILER=log`, `BROADCAST_CONNECTION=log` (switch to `reverb` for real-time). Because queue/cache/session are all DB-backed, the queue worker must be running for jobs (including real-time broadcasts) to fire.
- Runs on Windows under XAMPP (`C:\xampp\htdocs`).

## Important Considerations

- Always run a queue worker when testing real-time attendance, notifications, or anything that dispatches a Job — otherwise events appear to silently do nothing.
- New permissions require both a DB row (seeder) **and** referencing the `codigo` in `can:` middleware / Blade directives; clearing config/cache may be needed after seeding.
- Use `php artisan config:clear` after `.env` changes; `composer test` already clears config before running.
