# Dispatch: Worker M4 (E2E Test Runner & Asset Verification)

**Role**: E2E Verification Worker (`teamwork_worker`)
**Working Directory**: `c:\laragon\www\StudioKode`
**State Folder**: `c:\laragon\www\StudioKode\.agents\teamwork\worker_m4_1`

## Mandatory Reading:
1. `c:\laragon\www\StudioKode\.agents\teamwork\ORIGINAL_REQUEST.md` (read first!)
2. `c:\laragon\www\StudioKode\PROJECT.md`
3. `c:\laragon\www\StudioKode\.agents\teamwork\sub_orch_m4\SCOPE.md`
4. `c:\laragon\www\StudioKode\TEST_READY.md`

## Mandatory Integrity Warning:
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

## Assigned Tasks:
1. Execute `npm run build` in PowerShell and verify that Vite compiles all assets cleanly with 0 errors. Document the build duration, chunks generated, and exit code.
2. Clean test disks storage before testing:
   ```powershell
   if (Test-Path storage\framework\testing\disks) { Remove-Item -Recurse -Force storage\framework\testing\disks }
   ```
3. Execute the full PHPUnit test suite:
   ```powershell
   php artisan test
   ```
   Verify 100% pass across all 409+ tests (4,379+ assertions) with 0 failures and 0 errors.
4. Execute the dedicated theme test suite:
   ```powershell
   php artisan test tests/Feature/ThemeTransformationTest.php
   ```
   Verify all 12 test methods and 202 assertions pass.
5. If any test or build fails, diagnose and fix it while strictly respecting existing design tokens, anti-slop standards, ponytail conciseness, structural grids, and localization tokens (`#1C2A21`, `#F9F8F6`, `#E6E2DD`).
6. Write your comprehensive report in `c:\laragon\www\StudioKode\.agents\teamwork\worker_m4_1\handoff.md`.
7. Send a completion message back to the Sub-Orchestrator.


## 2026-11-10T14:01:11Z
You are the E2E Test Runner & Asset Verification Worker (teamwork_worker) for Milestone 4: e2e_verification_and_hardening in StudioKode.

Working Directory: c:\laragon\www\StudioKode
State Folder: c:\laragon\www\StudioKode\.agents\teamwork\worker_m4_1

CRITICAL MANDATORY READING (Read these first before running any command):
1. c:\laragon\www\StudioKode\.agents\teamwork\ORIGINAL_REQUEST.md
2. c:\laragon\www\StudioKode\PROJECT.md
3. c:\laragon\www\StudioKode\.agents\teamwork\sub_orch_m4\SCOPE.md
4. c:\laragon\www\StudioKode\TEST_READY.md
5. c:\laragon\www\StudioKode\.agents\teamwork\worker_m4_1\DISPATCH.md

MANDATORY INTEGRITY WARNING:
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

Your Tasks:
1. Initialize your state in c:\laragon\www\StudioKode\.agents\teamwork\worker_m4_1\progress.md.
2. Execute production build:
   npm run build
   Verify exit code 0, asset manifest, and build performance.
3. Clean testing disks storage:
   if (Test-Path storage\framework\testing\disks) { Remove-Item -Recurse -Force storage\framework\testing\disks }
4. Execute full PHPUnit test suite:
   php artisan test
   Verify 100% pass across all 409+ tests (4,379+ assertions) with 0 failures, 0 errors.
5. Execute theme-specific test suite:
   php artisan test tests/Feature/ThemeTransformationTest.php
   Verify all 12 test methods and 202 assertions pass.
6. If any failure occurs, diagnose root cause and resolve it cleanly while adhering to /design-taste-frontend, /antislop-ui, and /ponytail standards.
7. Write your detailed handoff report to:
   c:\laragon\www\StudioKode\.agents\teamwork\worker_m4_1\handoff.md
8. Send a message to your orchestrator with your verdict (PASS/FAIL), summary of test results, and report location.
