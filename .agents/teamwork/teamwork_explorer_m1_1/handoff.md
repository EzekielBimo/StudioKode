# Milestone 1 Bootstrap Sequence Analysis & Execution Plan

**Agent**: `teamwork_explorer_m1_1`  
**State Folder**: `c:\laragon\www\StudioKode\.agents\teamwork\teamwork_explorer_m1_1\`  
**Working Directory**: `c:\laragon\www\StudioKode`  
**Date**: 2026-11-08  

---

## 1. Observation

### 1.1 Host Environment & PATH Configuration
- **Active PHP CLI Resolution**:
  Command: `Get-Command php`
  Result: `C:\laragon\bin\php\php8.0.3\php.exe` (PHP version 8.0.3).
- **Installed Target PHP Runtime**:
  Path: `C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe`
  Version: `PHP 8.3.30 (cli) (built: Jan 13 2026 22:50:40) (ZTS Visual C++ 2019 x64)`.
- **Environment PATH Strings**:
  - `User PATH`: Contains `C:\laragon\bin\php\php8.0.3` before other toolchains.
  - `Machine PATH`: Does not define PHP.
  - Result: Bare `php` and `composer` commands automatically resolve to PHP 8.0.3, which is incompatible with Laravel 11 (`php: ^8.2.0`).

### 1.2 `php.ini` & Zip Extension Status
- **Target php.ini**:
  Path: `C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.ini`
  Line 832 verbatim:
  ```ini
  829:extension=sqlite3
  830:;extension=tidy
  831:extension=xsl
  832:;extension=zip
  833:;zend_extension=opcache
  ```
- **Extension DLL**:
  Path: `C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\ext\php_zip.dll` exists (382,464 bytes).
  Verification test: `php -d extension=zip -m` returns `zip` cleanly without errors or warnings.
- **Composer Diagnostics**:
  `composer diagnose` reported: `zip: extension not loaded, unzip present, 7-Zip not available`.

### 1.3 Target Directory State (`c:\laragon\www\StudioKode`)
- Command: `Get-ChildItem -Path "c:\laragon\www\StudioKode" -Force`
- Verbatim directory listing:
  ```
  Directory: C:\laragon\www\StudioKode
  Mode   LastWriteTime    Length Name
  ----   -------------    ------ ----
  d----- 11/8/2026 3:46 PM       .agents
  ```
- Non-empty directory collision:
  Running `composer create-project laravel/laravel:^11.0 .` fails with:
  `[InvalidArgumentException] Project directory "c:\laragon\www\StudioKode" is not empty.`

### 1.4 MySQL 8.4 Engine & Database Status
- Binary: `C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe`
- Process: Listening on port `3306` (127.0.0.1 and 0.0.0.0).
- Credentials: User `root`, password `""` (empty string).
- Existing databases: `company_db`, `dss_saw_mobil`, `information_schema`, `mysql`, `performance_schema`, `sys`.
- Target database `studiokode`: Does NOT exist yet.

### 1.5 Laravel 11 `.env` & Architecture Differences
- Laravel 11 defaults to SQLite (`DB_CONNECTION=sqlite`) in fresh `.env` files.
- The MySQL parameters (`DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_DATABASE=studiokode`, etc.) are commented out by default in Laravel 11's `.env.example`.
- Laravel 11 has a streamlined application structure:
  - Middlewares are registered in `bootstrap/app.php` via `->withMiddleware(function (Middleware $middleware) { ... })` instead of `app/Http/Kernel.php` (which no longer exists).
  - Routes are configured in `bootstrap/app.php` pointing to `routes/web.php`.

### 1.6 Server-Side Inertia Package Requirements
- Package: `inertiajs/inertia-laravel`
- Packagist query verification:
  - Latest versions: `v2.x` / `v3.x`
  - Platform requirements: `php: ^8.2.0`, `laravel/framework: ^11.35|^12.0|^13.0`, `ext-json: *`.
  - PHP 8.3.30 and Laravel 11 satisfy all version constraints.

---

## 2. Logic Chain

1. **PHP CLI Version Enforcement**:
   - *Observation*: Default PATH invokes PHP 8.0.3, but Laravel 11 requires `>= 8.2.0`. PHP 8.3.30 is located at `C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64`.
   - *Logic*: To prevent platform check failures in Composer and Artisan, execution must enforce PHP 8.3.30. Because PowerShell command sessions run in fresh environments where process environment variables do not persist across tool calls, every PowerShell script block must explicitly prepend `$env:PATH = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64;" + $env:PATH;`. In addition, setting `[Environment]::SetEnvironmentVariable('Path', ..., 'User')` ensures that new user shells also inherit PHP 8.3.30 by default.

2. **Native Zip Archive Activation**:
   - *Observation*: Line 832 of `C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.ini` contains `;extension=zip`.
   - *Logic*: Without `ext-zip`, Composer falls back to system unzip or slow Git clones, slowing down dependency downloads and potentially hitting GitHub rate limits. Uncommenting `extension=zip` in `php.ini` enables PHP's native `ZipArchive` extension.

3. **Isolated Temporary Scaffolding & Zero-Impact Move**:
   - *Observation*: `c:\laragon\www\StudioKode` contains `.agents/`. `composer create-project` refuses non-empty directories.
   - *Logic*:
     - Creating the project in a subdirectory `c:\laragon\www\StudioKode\_temp_laravel` keeps all operations on the exact same NTFS volume (`C:`).
     - Moving all files and directories (including dotfiles `.env`, `.env.example`, `.gitignore`, `.editorconfig`) from `_temp_laravel` to `c:\laragon\www\StudioKode` using PowerShell `Get-ChildItem -Force | Where-Object { $_.Name -ne '.agents' } | Move-Item` performs instantaneous, metadata-only NTFS folder moves.
     - Because `_temp_laravel` contains no `.agents` directory, the existing `.agents/` folder and all agent state are preserved completely undisturbed.
     - Once items are moved, removing the empty `_temp_laravel` leaves a clean Laravel 11 root.

4. **Database Provisioning**:
   - *Observation*: MySQL 8.4 is listening on port 3306 with empty root password. `studiokode` does not exist.
   - *Logic*: Executing `CREATE DATABASE IF NOT EXISTS \`studiokode\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;` via `mysql.exe` provisions the schema storage before Laravel migrations or `.env` connections are attempted.

5. **MySQL Connection Configuration**:
   - *Observation*: Laravel 11 defaults to SQLite in `.env`.
   - *Logic*: Updating `.env` to uncomment and assign `DB_CONNECTION=mysql`, `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_DATABASE=studiokode`, `DB_USERNAME=root`, and `DB_PASSWORD=` guarantees immediate connectivity with MySQL 8.4.

6. **Inertia.js Server Integration**:
   - *Observation*: Inertia React requires `inertiajs/inertia-laravel` in Composer, middleware registration in `bootstrap/app.php`, and a root view in `resources/views/app.blade.php`.
   - *Logic*: Running `composer require inertiajs/inertia-laravel` followed by `php artisan inertia:middleware` and appending `HandleInertiaRequests::class` to the web middleware stack in `bootstrap/app.php` establishes the full backend Inertia contract.

---

## 3. Caveats

1. **Subshell Environment Scoping**: In the tool execution environment, changes to `$env:PATH` inside one tool call do not persist to subsequent tool calls. **Crucial rule for Builder**: Every tool execution running PHP or Composer commands must start with `$env:PATH = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64;" + $env:PATH;`.
2. **Hidden Dotfiles during Relocation**: Using naive wildcards like `Move-Item _temp_laravel\* .` in PowerShell will miss hidden files like `.env`, `.env.example`, `.gitignore`, and `.editorconfig`. The relocation MUST use `Get-ChildItem -Path $tempDir -Force | Move-Item` to capture all hidden files and folders.
3. **Storage Symlink Elevation**: Windows typically requires Administrator privileges to create symbolic links (`mklink /D`). Survey 3 verified that directory junctions do not require elevation. If `php artisan storage:link` fails with an elevation error, the builder must execute `cmd.exe /c mklink /J public\storage storage\app\public`.
4. **Composer Process Timeout**: Downloading Laravel 11 and vendor dependencies may take 30–90 seconds depending on network latency. The builder agent should execute Composer with an adequate wait time (`WaitMsBeforeAsync: 10000` or run as managed task) and verify completion before proceeding to file relocation.
5. **Preservation of `.agents/`**: Under no circumstances should `Remove-Item` be called on the project root or `.agents/`. The cleanup command must target only the temporary directory: `Remove-Item -Path "c:\laragon\www\StudioKode\_temp_laravel" -Recurse -Force`.

---

## 4. Conclusion & Step-by-Step Execution Plan

The Builder agent should execute the following 6 sequential phases in order:

### Phase 1: Environment Variables & PATH Configuration
1. Prepend PHP 8.3.30 to the User environment variable `Path` to persist across new login sessions:
   ```powershell
   $userPath = [Environment]::GetEnvironmentVariable('Path', 'User')
   $php83Dir = 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64'
   if ($userPath -notmatch [regex]::Escape($php83Dir)) {
       $cleanPath = ($userPath -split ';' | Where-Object { $_ -and $_ -ne 'C:\laragon\bin\php\php8.0.3' }) -join ';'
       [Environment]::SetEnvironmentVariable('Path', "$php83Dir;$cleanPath", 'User')
   }
   ```
2. In all command executions, prepend `$env:PATH`:
   ```powershell
   $env:PATH = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64;" + $env:PATH
   ```

### Phase 2: Enable `extension=zip` in `php.ini`
1. Edit `C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.ini`:
   ```powershell
   $iniPath = 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.ini'
   $content = Get-Content -Path $iniPath -Raw
   if ($content -match ';extension=zip') {
       $updated = $content -replace ';extension=zip', 'extension=zip'
       Set-Content -Path $iniPath -Value $updated -NoNewline
   }
   ```
2. Verify:
   ```powershell
   & "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" -m | Select-String "zip"
   ```

### Phase 3: Bootstrap Laravel 11 into `_temp_laravel` & Relocate Files
1. Create project into `_temp_laravel`:
   ```powershell
   $env:PATH = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64;" + $env:PATH
   Set-Location -Path "c:\laragon\www\StudioKode"
   if (Test-Path "_temp_laravel") { Remove-Item -Path "_temp_laravel" -Recurse -Force }
   composer create-project laravel/laravel:^11.0 _temp_laravel --prefer-dist --no-interaction
   ```
2. Move all contents (including dotfiles) to project root without disturbing `.agents`:
   ```powershell
   $tempDir = "c:\laragon\www\StudioKode\_temp_laravel"
   $targetDir = "c:\laragon\www\StudioKode"

   Get-ChildItem -Path $tempDir -Force | ForEach-Object {
       if ($_.Name -ne '.agents') {
           Move-Item -Path $_.FullName -Destination $targetDir -Force
       }
   }
   Remove-Item -Path $tempDir -Recurse -Force
   ```
3. Verify files and artisan:
   ```powershell
   $env:PATH = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64;" + $env:PATH
   Set-Location -Path "c:\laragon\www\StudioKode"
   php artisan --version
   ```

### Phase 4: Create MySQL Database `studiokode`
1. Ensure MySQL is running and create the database:
   ```powershell
   & "C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe" -u root -e "CREATE DATABASE IF NOT EXISTS \`studiokode\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   ```
2. Verify creation:
   ```powershell
   & "C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe" -u root -e "SHOW DATABASES LIKE 'studiokode';"
   ```

### Phase 5: Configure `.env` for MySQL & StudioKode
1. Update `.env` in `c:\laragon\www\StudioKode\.env`:
   ```powershell
   $envFile = "c:\laragon\www\StudioKode\.env"
   $content = Get-Content -Path $envFile -Raw

   # Update APP_NAME
   $content = $content -replace '^APP_NAME=.*$', 'APP_NAME="StudioKode"'

   # Update Database Connection settings
   $content = $content -replace '^DB_CONNECTION=.*$', 'DB_CONNECTION=mysql'
   
   if ($content -match '# DB_HOST=') {
       $content = $content -replace '# DB_HOST=127.0.0.1', 'DB_HOST=127.0.0.1'
       $content = $content -replace '# DB_PORT=3306', 'DB_PORT=3306'
       $content = $content -replace '# DB_DATABASE=.*$', 'DB_DATABASE=studiokode'
       $content = $content -replace '# DB_USERNAME=.*$', 'DB_USERNAME=root'
       $content = $content -replace '# DB_PASSWORD=.*$', 'DB_PASSWORD='
   } else {
       $content = $content -replace '^DB_HOST=.*$', 'DB_HOST=127.0.0.1'
       $content = $content -replace '^DB_PORT=.*$', 'DB_PORT=3306'
       $content = $content -replace '^DB_DATABASE=.*$', 'DB_DATABASE=studiokode'
       $content = $content -replace '^DB_USERNAME=.*$', 'DB_USERNAME=root'
       $content = $content -replace '^DB_PASSWORD=.*$', 'DB_PASSWORD='
   }

   Set-Content -Path $envFile -Value $content -NoNewline
   ```
2. Verify database connectivity through Artisan:
   ```powershell
   $env:PATH = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64;" + $env:PATH
   Set-Location -Path "c:\laragon\www\StudioKode"
   php artisan db:show
   ```

### Phase 6: Install Server-Side Inertia Package & Scaffold Middleware
1. Install package:
   ```powershell
   $env:PATH = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64;" + $env:PATH
   Set-Location -Path "c:\laragon\www\StudioKode"
   composer require inertiajs/inertia-laravel --no-interaction
   ```
2. Publish Inertia middleware:
   ```powershell
   $env:PATH = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64;" + $env:PATH
   Set-Location -Path "c:\laragon\www\StudioKode"
   php artisan inertia:middleware
   ```
3. Register middleware in `bootstrap/app.php`:
   Ensure `HandleInertiaRequests::class` is appended to the web middleware stack in `bootstrap/app.php`:
   ```php
   use App\Http\Middleware\HandleInertiaRequests;

   return Application::configure(basePath: dirname(__DIR__))
       ->withRouting(
           web: __DIR__.'/../routes/web.php',
           commands: __DIR__.'/../routes/console.php',
           health: '/up',
       )
       ->withMiddleware(function (Middleware $middleware) {
           $middleware->web(append: [
               HandleInertiaRequests::class,
           ]);
       })
       ->withExceptions(function (Exceptions $exceptions) {
           //
       })->create();
   ```
4. Create root template `resources/views/app.blade.php`:
   ```blade
   <!DOCTYPE html>
   <html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
   <head>
       <meta charset="utf-8">
       <meta name="viewport" content="width=device-width, initial-scale=1">
       <title inertia>{{ config('app.name', 'StudioKode') }}</title>
       @viteReactRefresh
       @vite(['resources/js/app.jsx', "resources/js/Pages/{$page['component']}.jsx"])
       @inertiaHead
   </head>
   <body class="font-sans antialiased bg-[#F9F8F6] text-[#1A1A1A]">
       @inertia
   </body>
   </html>
   ```

---

## 5. Verification Method

To independently verify the successful execution of this bootstrap sequence, run the following verification suite:

1. **Verify PHP 8.3 & Zip Extension**:
   ```powershell
   $env:PATH = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64;" + $env:PATH
   php -v
   php -m | Select-String "zip"
   ```
   *Pass criteria*: Outputs `PHP 8.3.30` and `zip`.

2. **Verify Laravel 11 Scaffolding & Root Files**:
   ```powershell
   Test-Path "c:\laragon\www\StudioKode\artisan"
   Test-Path "c:\laragon\www\StudioKode\composer.json"
   Test-Path "c:\laragon\www\StudioKode\.env"
   Test-Path "c:\laragon\www\StudioKode\.agents\teamwork"
   ```
   *Pass criteria*: All return `True`.

3. **Verify Database Connectivity**:
   ```powershell
   $env:PATH = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64;" + $env:PATH
   Set-Location -Path "c:\laragon\www\StudioKode"
   php artisan db:show
   ```
   *Pass criteria*: Successfully connects to MySQL 8.4 database `studiokode` without errors.

4. **Verify Server-Side Inertia**:
   ```powershell
   $env:PATH = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64;" + $env:PATH
   Set-Location -Path "c:\laragon\www\StudioKode"
   composer show inertiajs/inertia-laravel
   Test-Path "app\Http\Middleware\HandleInertiaRequests.php"
   Test-Path "resources\views\app.blade.php"
   ```
   *Pass criteria*: Composer shows package details; both middleware and blade root view files exist.

**Invalidation Conditions**:
- If `composer create-project` is run directly in `c:\laragon\www\StudioKode`, it will fail because `.agents` exists.
- If `$env:PATH` is omitted in a command session, commands will fall back to PHP 8.0.3 and fail.
- If `.env` is not switched from `sqlite` to `mysql`, migrations will default to creating a SQLite database.
