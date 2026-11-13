# Handoff Report — Explorer M4.2: Filesystem & Storage Symlink (Feature 26)

## 1. Observation

### 1.1 Filesystem Configuration Failure in Test Suite
Direct execution of the project test suite (`php artisan test`) revealed exactly 1 failing test out of 293 tests:
- **Test File**: `tests/Feature/Admin/AdminPerformanceTest.php`
- **Method**: `test_public_filesystem_configuration` (lines 171–178)
- **Verbatim Error Output**:
```text
   FAIL  Tests\Feature\Admin\AdminPerformanceTest
  ⨯ public filesystem configuration                                                                              0.55s  
  ────────────────────────────────────────────────────────────────────────────────────────────────────────────────────  
   FAILED  Tests\Feature\Admin\AdminPerformanceTest > public filesystem configuration                                   
  Failed asserting that two strings are equal.
--- Expected
+++ Actual
@@ @@
-'/storage'
+'http://localhost/storage'

  at tests\Feature\Admin\AdminPerformanceTest.php:177
    173▕         $config = config('filesystems.disks.public');
    174▕ 
    175▕         $this->assertNotNull($config, 'Public disk must be configured in filesystems.php');
    176▕         $this->assertEquals('local', $config['driver']);
  ➜ 177▕         $this->assertEquals('/storage', $config['url']);
    178▕     }
```

### 1.2 Public Disk Configuration in `config/filesystems.php`
Inspection of `config/filesystems.php` lines 39–45:
```php
        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],
```
- Line 42 sets `'url' => env('APP_URL').'/storage'`.
- In `.env`, line 6 defines `APP_URL=http://localhost`.
- When `config('filesystems.disks.public.url')` is resolved, it returns `'http://localhost/storage'`.
- Neither `.env`, `.env.example`, nor `phpunit.xml` defines `FILESYSTEM_PUBLIC_URL`.

### 1.3 Windows Storage Symlink Creation & Behavior
1. Executed `php artisan storage:link`:
   - Output: `INFO The [C:\laragon\www\StudioKode\public\storage] link has been connected to [C:\laragon\www\StudioKode\storage\app/public].`
2. Inspected created link using PowerShell `Get-Item public/storage | Format-List FullName, Mode, LinkType, Target`:
   - `FullName`: `C:\laragon\www\StudioKode\public\storage`
   - `Mode`: `d----l`
   - `LinkType`: `Junction`
   - `Target`: `{C:\laragon\www\StudioKode\storage\app\public}`
3. Checked framework implementation in `vendor/laravel/framework/src/Illuminate/Filesystem/Filesystem.php` (lines 354–364):
   ```php
   if (! windows_os()) {
       if (function_exists('symlink')) {
           return symlink($target, $link);
       } else {
           return exec('ln -s '.escapeshellarg($target).' '.escapeshellarg($link)) !== false;
       }
   }

   $mode = $this->isDirectory($target) ? 'J' : 'H';

   exec("mklink /{$mode} ".escapeshellarg($link).' '.escapeshellarg($target));
   ```
   On Windows, Laravel executes `mklink /J` to produce an NTFS directory junction. NTFS junctions do not require Windows Developer Mode or administrator privileges.
4. Tested `php artisan storage:link --force` when the junction already exists:
   - Output: `ERROR The [C:\laragon\www\StudioKode\public\storage] link already exists.`
   - Cause: In PHP on Windows, `is_link('public/storage')` returns `bool(false)` for NTFS junctions because PHP stat treats junctions as directories (`is_dir` = true), not POSIX symlinks.
   - In `vendor/laravel/framework/src/Illuminate/Foundation/Console/StorageLinkCommand.php` (line 76):
     `return is_link($link) && $force;`
     Because `is_link($link)` is `false`, `isRemovableSymlink()` returns `false`, causing the command to report an error instead of replacing the junction.
5. Tested recreation via command prompt:
   - Command: `cmd /c rmdir public\storage` removes the junction in < 10ms without deleting contents of `storage/app/public`.
   - Subsequent `php artisan storage:link` recreates the junction cleanly.

### 1.4 File Accessibility via `/storage/projects/...`
1. Created test asset at `storage/app/public/projects/test.txt`.
2. Verified local path resolution through junction:
   - `Test-Path public/storage/projects/test.txt` returned `True`.
   - `Get-Content public/storage/projects/test.txt` returned `test content`.
3. Verified HTTP serving through web server:
   - Ran PHP development server targeting `public` root on `127.0.0.1:8999`.
   - `Invoke-WebRequest -Uri 'http://127.0.0.1:8999/storage/projects/test.txt'` returned `StatusCode: 200`.
4. Verified frontend URL resolution:
   - In `resources/js/Components/Admin/ProjectGalleryManager.jsx` (lines 20–30), `resolveImageUrl(path)` maps relative storage paths (`projects/...`) to `/storage/projects/...`.
   - In `public/.htaccess`, Apache's `RewriteCond %{REQUEST_FILENAME} !-f` allows Apache to bypass PHP and directly serve files present under `public/storage/...`.

---

## 2. Logic Chain

1. **Premise 1 (Observation 1.1)**: `tests/Feature/Admin/AdminPerformanceTest.php:177` explicitly asserts `$this->assertEquals('/storage', $config['url'])`.
2. **Premise 2 (Observation 1.2)**: `config/filesystems.php:42` currently specifies `'url' => env('APP_URL').'/storage'`. In development and test environments where `APP_URL=http://localhost`, `$config['url']` evaluates to `'http://localhost/storage'`, directly contradicting the assertion.
3. **Premise 3 (Observation 1.4)**: Modern Inertia SPAs and the existing frontend helper (`resolveImageUrl` in `resources/js/Components/Admin/ProjectGalleryManager.jsx`) consume relative URLs (`/storage/...`), avoiding cross-origin, protocol mismatch (HTTP vs HTTPS), and port mismatch issues.
4. **Premise 4**: Changing line 42 of `config/filesystems.php` to `'url' => env('FILESYSTEM_PUBLIC_URL', '/storage')` satisfies:
   - The test assertion (`/storage` when `FILESYSTEM_PUBLIC_URL` is omitted).
   - 12-factor application design, allowing production deployments (e.g. S3/CloudFront or CDN custom domain) to configure `FILESYSTEM_PUBLIC_URL` via environment variables without editing PHP source code.
5. **Premise 5 (Observation 1.3)**: `public/storage` was initially missing from the workspace. Executing `php artisan storage:link` creates the NTFS directory junction `public/storage -> storage/app/public`.
6. **Premise 6 (Observation 1.3)**: Because PHP on Windows evaluates `is_link()` as `false` for NTFS junctions, `php artisan storage:link --force` cannot overwrite an existing junction. The required recreation procedure on Windows is `cmd /c rmdir public\storage && php artisan storage:link`.
7. **Premise 7 (Observation 1.4)**: Once the junction is established, static assets placed under `storage/app/public/projects/` are immediately accessible over HTTP via `/storage/projects/...` with HTTP 200, matching the behavior specified in Feature 26.

---

## 3. Caveats

1. **Admin Privileges / Windows Mode**: NTFS Junctions (`mklink /J`) work without administrator elevation on standard NTFS volumes on Windows. However, if the project is ever moved to a network share or FAT32/exFAT drive, junction creation will fail.
2. **PHP Built-in Server vs Laragon Apache**: In Apache (Laragon), `.htaccess` handles the static file fallback. In PHP's built-in CLI web server (`php -S`), junctions are resolved natively by the Windows OS kernel filesystem driver. Both serve static assets cleanly at HTTP 200.
3. **Scope Constraint**: As a read-only investigation agent, no source code files in `app/` or `config/` were modified. The proposed change to `config/filesystems.php` is provided below as a diff patch for the builder/implementer.

---

## 4. Conclusion

1. **Configuration Fix Needed**:
   In `config/filesystems.php` at line 42, replace:
   ```php
   'url' => env('APP_URL').'/storage',
   ```
   with:
   ```php
   'url' => env('FILESYSTEM_PUBLIC_URL', '/storage'),
   ```
   This will immediately resolve the 1 failing test in `AdminPerformanceTest.php`, bringing the entire test suite to 100% pass (293/293 passing).

2. **Storage Symlink Status**:
   - `public/storage` currently exists as an active NTFS Junction connected to `storage/app/public`.
   - If recreation is ever required on Windows, use:
     ```cmd
     cmd /c rmdir public\storage
     php artisan storage:link
     ```

3. **HTTP Asset Serving**:
   - File accessibility via `/storage/projects/...` is verified and operational.

### Proposed Code Diff
```diff
--- a/config/filesystems.php
+++ b/config/filesystems.php
@@ -39,7 +39,7 @@ return [
         'public' => [
             'driver' => 'local',
             'root' => storage_path('app/public'),
-            'url' => env('APP_URL').'/storage',
+            'url' => env('FILESYSTEM_PUBLIC_URL', '/storage'),
             'visibility' => 'public',
             'throw' => false,
         ],
```

---

## 5. Verification Method

### Test Commands
1. Run the target performance configuration test:
   ```bash
   php artisan test --filter=test_public_filesystem_configuration
   ```
   *Expected result after applying proposed change: PASS (1 test, 3 assertions).*

2. Run the complete test suite:
   ```bash
   php artisan test
   ```
   *Expected result: 293 passed, 0 failed (2,578 assertions).*

3. Inspect the storage link on Windows:
   ```powershell
   Get-Item public/storage | Format-List FullName, Mode, LinkType, Target
   ```
   *Expected result: `LinkType : Junction`, `Target : {C:\laragon\www\StudioKode\storage\app\public}`.*

4. Verify HTTP file accessibility:
   ```powershell
   powershell -Command "New-Item -ItemType Directory -Force -Path storage/app/public/projects; 'ping' | Out-File -FilePath storage/app/public/projects/probe.txt; Test-Path public/storage/projects/probe.txt; Remove-Item storage/app/public/projects/probe.txt"
   ```
   *Expected result: `True`.*

### Invalidation Conditions
- If `FILESYSTEM_PUBLIC_URL` is set in `.env` to a non-relative URL while running tests without testing environment isolation, `test_public_filesystem_configuration` would fail.
- If `public/storage` is deleted or created as a regular directory instead of a junction, static asset requests will 404 or fail to link to uploaded media.
