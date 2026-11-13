# Dispatch: Worker M1 (Milestone 1 — admin_auth_foundation Implementation)

## Scope & Objective
Implement Milestone 1 (`admin_auth_foundation`) covering Features 1 through 7 in `PROJECT.md`:
1. `app/Http/Controllers/Admin/AuthController.php`:
   - `create`: Renders `Admin/Auth/Login` (for guests).
   - `store`: Validates email (required, email) and password (required). Authenticates via `Auth::attempt($credentials, $request->boolean('remember'))`. On success, calls `$request->session()->regenerate()` and redirects intended to `/admin` with Indonesian flash `session()->flash('success', 'Selamat datang kembali di Panel Admin StudioKode.')`. On failure, returns 422 with Indonesian error message: `'email' => 'Kredensial yang diberikan tidak cocok dengan data kami.'`.
   - `destroy`: `Auth::logout()`, invalidates session, regenerates CSRF token, redirects to `/admin/login` with flash `'success' => 'Anda telah berhasil keluar dari sesi admin.'`.
2. `routes/web.php`:
   - Guest routes:
     - `Route::get('/admin/login', [App\Http\Controllers\Admin\AuthController::class, 'create'])->name('login');`
     - `Route::post('/admin/login', [App\Http\Controllers\Admin\AuthController::class, 'store'])->name('admin.login.store');`
   - Protected routes group:
     - `Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () { Route::post('/logout', [App\Http\Controllers\Admin\AuthController::class, 'destroy'])->name('logout'); Route::get('/', function() { return redirect()->route('admin.dashboard'); }); });`
     (Or temporary route for `/admin` redirecting to dashboard)
3. `database/seeders/DatabaseSeeder.php`:
   - Import `App\Models\User` and `Illuminate\Support\Facades\Hash`.
   - Use `User::updateOrCreate(['email' => 'admin@studiokode.com'], ['name' => 'Admin StudioKode', 'password' => Hash::make('password')]);` so it runs idempotently.
4. `app/Http/Middleware/HandleInertiaRequests.php`:
   - Share `'auth'` containing `'user' => $request->user() ? ['id' => $request->user()->id, 'name' => $request->user()->name, 'email' => $request->user()->email] : null`.
5. `resources/js/lib/id.js`:
   - Create centralized Indonesian localization dictionary matching `explorer_m1_2` specification.
6. `resources/js/Pages/Admin/Auth/Login.jsx`:
   - Create React 18 / Inertia `useForm` login component matching StudioKode botanical aesthetic (`#F9F8F6`, `#1C2A21`, `#E6E2DD`), Framer Motion entrance animation, full Indonesian labels and error states.
7. `tests/Feature/ChallengerM1_2_RouteContractTest.php`:
   - In `test_404_response_for_non_existent_system_routes` (around line 409), replace `$bogusRoutes` with `['/bogus-admin', '/unknown-dashboard', '/api/projects', '/portfolio', '/about-us']`.

## Reference Materials
- `c:\laragon\www\StudioKode\.agents\teamwork\ORIGINAL_REQUEST.md`
- `c:\laragon\www\StudioKode\PROJECT.md`
- `c:\laragon\www\StudioKode\TEST_INFRA.md`
- `c:\laragon\www\StudioKode\TEST_READY.md`
- `c:\laragon\www\StudioKode\.agents\teamwork\explorer_m1_2\handoff.md`
- `c:\laragon\www\StudioKode\.agents\teamwork\explorer_m1_3\handoff.md`
- `c:\laragon\www\StudioKode\.agents\teamwork\explorer_survey_1\handoff.md`

## Verification Requirements
Worker MUST execute and document passing results for:
1. `php artisan db:seed`
2. `php artisan test tests/Feature/Admin/AdminAuthTest.php`
3. `php artisan test tests/Feature/ChallengerM1_2_RouteContractTest.php`
4. `npm run build`

## Mandatory Integrity Warning
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

Write your report to `c:\laragon\www\StudioKode\.agents\teamwork\worker_m1\handoff.md`.
