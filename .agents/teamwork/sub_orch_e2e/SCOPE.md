# Scope: E2E Testing Track (`theme_e2e_verification`)

## Architecture
Automated test suite combining PHPUnit Feature tests (`tests/Feature/ThemeTransformationTest.php`), build validation (`npm run build`), existing PHPUnit suite regression pass (all 389 tests), and publication of `TEST_READY.md` for the Smoked Obsidian / Dark Moss Glass theme transformation.

## Feature Inventory
| # | Feature | Description | Milestone | Source |
|---|---------|-------------|-----------|--------|
| 1 | Build & Asset Verification | Verify `npm run build` succeeds cleanly, check bundle sizes & manifests | E2E-M1 | Dispatch |
| 2 | Full PHPUnit Suite Pass | Run and verify all 389 tests in `tests/` pass with 100% success | E2E-M1 | Dispatch |
| 3 | Visual & Token Verification | Verify `app.blade.php` meta theme-color (`#0B0E0C`), dark body classes, dark selection, obsidian/moss tokens | E2E-M1 | Dispatch |
| 4 | Layout & Responsive Preservation | Verify structural grid classes (`grid-cols-*`, `max-w-7xl`, `min-h-screen`) across pages/components | E2E-M1 | Dispatch |
| 5 | TEST_READY.md Publication | Update `TEST_READY.md` and `TEST_INFRA.md` documenting the theme verification test suite | E2E-M1 | Dispatch |

## Milestones
| # | Name | Scope | Dependencies | Status |
|---|------|-------|-------------|--------|
| 1 | `e2e_theme_test_implementation` | Implement `ThemeTransformationTest.php`, update `TEST_READY.md` & `TEST_INFRA.md`, verify build & 389+ tests | none | DONE |

## Interface Contracts
- Tests must be runnable via `php artisan test tests/Feature/ThemeTransformationTest.php`.
- Build must be runnable via `npm run build`.
- Full suite runnable via `php artisan test`.
