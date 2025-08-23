# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 12 application for "Sistema de Asistencia" (Attendance System) that manages student enrollments, attendance tracking, and teacher scheduling for an educational institution called CEPRE.

## Technology Stack

- **Backend**: Laravel 12, PHP 8.2+
- **Database**: SQLite (configurable to MySQL/PostgreSQL)
- **Frontend**: Blade templates, JavaScript, Tailwind CSS
- **Build Tools**: Vite, NPM
- **Authentication**: Laravel's built-in authentication with email verification
- **Additional**: Laravel Excel for exports, DomPDF for document generation, Laravel Reverb for real-time features

## Common Development Commands

### Initial Setup
```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

### Development Server
```bash
# Option 1: Run all services concurrently
composer run dev

# Option 2: Run individually
php artisan serve              # Start Laravel server
npm run dev                     # Start Vite dev server
php artisan queue:listen        # Start queue worker
```

### Build and Testing
```bash
npm run build                    # Build frontend assets
php artisan test                 # Run PHPUnit tests
php artisan test --filter=TestName  # Run specific test
```

### Database Commands
```bash
php artisan migrate              # Run migrations
php artisan migrate:rollback     # Rollback last migration
php artisan migrate:fresh        # Fresh migration (drops all tables)
php artisan db:seed              # Run database seeders
```

### Cache Management
```bash
php artisan config:clear         # Clear config cache
php artisan cache:clear          # Clear application cache
php artisan route:clear          # Clear route cache
php artisan view:clear           # Clear view cache
```

## Architecture Overview

### MVC Pattern
The application follows Laravel's MVC architecture:
- **Models** (`app/Models/`): Eloquent ORM models representing database entities
- **Views** (`resources/views/`): Blade templates organized by feature
- **Controllers** (`app/Http/Controllers/`): Handle HTTP requests and business logic

### Key Modules

1. **Authentication System**
   - Custom authentication with role-based access control (RBAC)
   - Roles: admin, profesor, estudiante, padre, postulante
   - Email verification for postulantes (applicants)

2. **Student Management**
   - Inscriptions (enrollments) with cycle, career, and shift selection
   - Postulations (applications) with document upload capabilities
   - Constancia generation (PDF certificates)

3. **Attendance Tracking**
   - Real-time attendance monitoring for students
   - Teacher attendance with biometric support
   - Attendance events and history tracking

4. **Academic Management**
   - Ciclos (academic cycles/terms)
   - Carreras (career programs)
   - Aulas (classrooms)
   - Turnos (shifts)
   - Cursos (courses)
   - Horarios (schedules)

5. **API Layer**
   - RESTful API controllers in `app/Http/Controllers/Api/`
   - Used for AJAX operations and real-time features

### Database Structure

The application uses a comprehensive migration system with tables for:
- User management (users, roles, permissions, user_roles)
- Academic structure (ciclos, carreras, aulas, turnos, cursos)
- Enrollments (inscripciones, postulaciones)
- Attendance (registro_asistencia, asistencias_docentes, asistencia_eventos)
- Scheduling (horarios_docentes)
- Notifications and announcements (anuncios, notifications)

### Frontend Organization

- **Public Assets** (`public/assets/`): Static CSS, JS, images
- **Resources** (`resources/`): Source files for compilation
- **Blade Layouts** (`resources/views/layouts/`): Base templates
- **Feature Views** (`resources/views/`): Organized by module (inscripciones, asistencia, etc.)

### Key Configuration Files

- `.env`: Environment variables (database, mail, app settings)
- `config/`: Laravel configuration files
- `routes/web.php`: Web routes definition
- `routes/api.php`: API routes definition

## Important Considerations

1. **Windows Environment**: Project runs on Windows with Apache (C:\Apache24\htdocs)
2. **Database**: Default SQLite database at `database/database.sqlite`
3. **Session Storage**: Database-based sessions
4. **Queue System**: Database queue driver for background jobs
5. **Mail**: Currently set to log driver for development
6. **Real-time Features**: Uses Laravel Reverb for WebSocket connections