# Dispatch: Explorer M1.3 (Database Seeder, Middleware Shared Props & Test Contract)

## Scope: Milestone 1 — admin_auth_foundation
Investigate and design the seeder, shared auth state, and route contract compatibility:
1. `database/seeders/DatabaseSeeder.php`:
   - Seed default admin user: `name => 'Admin StudioKode'`, `email => 'admin@studiokode.com'`, `password => Hash::make('password')`.
   - Use `User::firstOrCreate` or `User::updateOrCreate` so it runs idempotently without crashing if seeder is run multiple times.
2. `app/Http/Middleware/HandleInertiaRequests.php`:
   - Expose `auth.user` in `share()` method:
     ```php
     'auth' => [
         'user' => $request->user() ? [
             'id' => $request->user()->id,
             'name' => $request->user()->name,
             'email' => $request->user()->email,
         ] : null,
     ],
     ```
3. `tests/Feature/ChallengerM1_2_RouteContractTest.php`:
   - In `test_404_response_for_non_existent_system_routes` (around line 409), the array contains `['/admin', '/dashboard', ...]`.
   - Because `/admin` is now an active protected route (returning 302 to login), update the test array to use non-existent system routes such as `['/bogus-admin', '/unknown-dashboard', '/api/projects', '/portfolio', '/about-us']`.
   - Verify that this change allows both public contract tests and new admin auth tests to pass cleanly.

Read:
- `c:\laragon\www\StudioKode\.agents\teamwork\ORIGINAL_REQUEST.md`
- `c:\laragon\www\StudioKode\PROJECT.md`

Write your recommendations to `c:\laragon\www\StudioKode\.agents\teamwork\explorer_m1_3\handoff.md`.


## 2026-11-09T11:53:18Z
You are Explorer M1.3 investigating the Database Seeder, Middleware Shared Props, and Route Contract Test resolution for StudioKode Milestone 1.

MANDATORY FIRST STEP:
Read c:\laragon\www\StudioKode\.agents\teamwork\ORIGINAL_REQUEST.md, c:\laragon\www\StudioKode\PROJECT.md, and c:\laragon\www\StudioKode\.agents\teamwork\explorer_m1_3\DISPATCH.md before starting work.

State folder: c:\laragon\www\StudioKode\.agents\teamwork\explorer_m1_3
Working directory: c:\laragon\www\StudioKode

Your assignment:
1. Analyze implementation specifics for admin seeder in `DatabaseSeeder.php`, `auth.user` prop sharing in `HandleInertiaRequests.php`, and adapting `ChallengerM1_2_RouteContractTest.php:409`.
2. Provide exact code snippets and verification checks.
3. Write your report to c:\laragon\www\StudioKode\.agents\teamwork\explorer_m1_3\handoff.md.

Send a message when your handoff is ready.
