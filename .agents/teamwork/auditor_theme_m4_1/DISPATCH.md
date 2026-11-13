# Dispatch: Forensic Auditor Theme M4 (`auditor_theme_m4_1`)

**Role**: Forensic Integrity Auditor (`teamwork_auditor`)
**Working Directory**: `c:\laragon\www\StudioKode`
**State Folder**: `c:\laragon\www\StudioKode\.agents\teamwork\auditor_theme_m4_1`

## Mandatory Reading:
1. `c:\laragon\www\StudioKode\.agents\teamwork\ORIGINAL_REQUEST.md` (read first!)
2. `c:\laragon\www\StudioKode\PROJECT.md`
3. `c:\laragon\www\StudioKode\.agents\teamwork\sub_orch_m4\SCOPE.md`
4. `c:\laragon\www\StudioKode\TEST_READY.md`
5. `c:\laragon\www\StudioKode\.agents\teamwork\worker_m4_1\handoff.md`

## Audit Instructions:
Execute comprehensive forensic integrity checks across all 32 features and codebase changes:
1. Static analysis: inspect git diff, modified JSX components, layouts, Blade root, CSS, and test files.
2. Check for:
   - Hardcoded test outputs or string sniffing.
   - Dummy or facade implementations (e.g., returning static mocks rather than genuine rendering/logic).
   - Test-specific branching (e.g., `if (testing) return true`).
   - Fabrication of verification results or bypass of required build/test processes.
3. Verify that all 32 features are implemented genuinely with authentic logic, proper state handling, and uncompromised security.
4. Record your detailed findings and explicit verdict (**CLEAN** or **INTEGRITY VIOLATION**) in `c:\laragon\www\StudioKode\.agents\teamwork\auditor_theme_m4_1\handoff.md`.
5. Send a message to the Sub-Orchestrator with your verdict.

