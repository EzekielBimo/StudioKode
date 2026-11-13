# Dispatch for Worker M1

Scope: Milestone 1 — Backend Architecture, Database, Routing & Feature Tests.
Working directory: c:\laragon\www\StudioKode
State folder: c:\laragon\www\StudioKode\.agents\teamwork\teamwork_worker_m1\

Read:
- c:\laragon\www\StudioKode\.agents\teamwork\ORIGINAL_REQUEST.md
- c:\laragon\www\StudioKode\.agents\teamwork\orchestrator\PROJECT.md
- c:\laragon\www\StudioKode\.agents\teamwork\teamwork_explorer_m1_1\handoff.md
- c:\laragon\www\StudioKode\.agents\teamwork\teamwork_explorer_m1_2\handoff.md
- c:\laragon\www\StudioKode\.agents\teamwork\teamwork_explorer_m1_3\handoff.md

## MANDATORY INTEGRITY WARNING
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

## Tasks
1. Execute PHP 8.3 PATH configuration and enable zip extension in `C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.ini`.
2. Scaffold Laravel 11 into a temp directory and relocate all files into `c:\laragon\www\StudioKode` preserving `.agents/`.
3. Create MySQL database `studiokode` on port 3306 using `mysql -u root`.
4. Configure `.env` (`DB_CONNECTION=mysql`, `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_DATABASE=studiokode`, `DB_USERNAME=root`, `DB_PASSWORD=`).
5. Install `composer require inertiajs/inertia-laravel` with PHP 8.3.
6. Create database migrations for `projects`, `project_images`, and `inquiries` tables. Verify zero category tables.
7. Create Eloquent models `Project`, `ProjectImage`, `Inquiry`, and model factories.
8. Create `DatabaseSeeder.php` seeding 6 realistic luxury garden architecture projects with gallery images.
9. Execute `php artisan migrate:fresh --seed` and verify database tables and record counts.
10. Create `resources/views/app.blade.php`, `HandleInertiaRequests` middleware (registered in `bootstrap/app.php`).
11. Create `HomeController`, `ProjectController`, `PageController`, `InquiryController` with validation and flash message.
12. Configure `routes/web.php` for all required routes.
13. Create `tests/Feature/RouteTest.php`, `tests/Feature/InquiryTest.php`, and `tests/Feature/SeederTest.php`.
14. Run `php artisan test` and verify 100% tests pass.
15. Write full handoff report to `c:\laragon\www\StudioKode\.agents\teamwork\teamwork_worker_m1\handoff.md` and message orchestrator.
