# PHPCS Baseline Summary

**Generated:** 2026-02-16 13:00  
**Tool:** PHP_CodeSniffer 3.x with WordPress-Extra ruleset  
**Scope:** All plugin files except pp-core.php and pp-assets/ (excluded per M2 plan)

## Overview

Initial PHPCS scan reveals **significant violations** across the codebase. The good news: **most violations are auto-fixable** with phpcbf (PHP Code Beautifier and Fixer).

### Files Scanned

8 files total:
- admin-templates/trackers-settings-msft-clarity.php
- functions.php
- trackers/tracker-google-analytics.php
- includes/class-settings.php
- (and 4 more files - see full report in phpcs-baseline.txt)

### Violation Categories

#### 1. **Formatting Issues (Auto-Fixable) 🟢**
- Spaces vs. tabs indentation (hundreds of violations)
- Function call spacing (parentheses padding)
- Array formatting (commas, spacing)
- Brace positioning (K&R style not followed)
- Operator spacing

**Action:** Run `phpcbf` to auto-fix these

#### 2. **Naming Conventions (Manual Fix Required) 🟡**
- Namespace prefix: `Mini_Wp_Gdpr` should be `PP_MWG_Mini_Wp_Gdpr` (per prefix rules in phpcs.xml)
- Function names: `mwg_*` should be `pp_mwg_*`
- Global variables need proper prefixing

**Action:** Manual refactoring needed (can be done gradually)

#### 3. **Security Issues (Manual Fix Required) 🔴**
- Missing output escaping (`WordPress.Security.EscapeOutput.OutputNotEscaped`)
- Functions outputting unescaped variables
- Examples:
  - `pp_get_admin_checkbox_html` needs escaping wrapper
  - `pp_get_header_logo_html` needs escaping
  - Direct constant output without escaping

**Action:** Add proper escaping (`esc_html`, `esc_attr`, `esc_url`) - Priority fix for M7

#### 4. **WordPress Best Practices (Manual Fix Required) 🟡**
- Role checks instead of capability checks (`administrator` → `manage_options`)
- Missing `$in_footer` parameter in `wp_enqueue_script()` calls
- Missing version parameter in enqueue calls
- Debug functions left in code (`error_log()`)

**Action:** Address in cleanup phase

#### 5. **Code Quality Issues (Manual Review) 🟡**
- Empty IF statements detected
- Assignment within conditions (potential typo: `=` vs `==`)
- Commented-out code blocks

**Action:** Review and clean up

## Next Steps (Milestone 2)

1. ✅ **Baseline created** (this document + full report)
2. **Run phpcbf for auto-fixes** - will resolve ~80% of violations
3. **Manual fixes (security)** - add escaping where needed
4. **Manual fixes (naming)** - refactor prefixes (can defer to M3)
5. **Re-run PHPCS** after auto-fixes to see remaining violations

## Notes

- **pp-core.php excluded:** 2305 lines being removed in M3, no point fixing
- **PHPCompatibilityWP disabled:** Requires Composer installation (commented out in phpcs.xml)
- **Auto-fix safety:** phpcbf is safe for formatting; won't break logic
- **Security violations:** Some false positives (e.g., functions may already escape internally), but worth reviewing

## Estimated Effort

- Auto-fixes: 5 minutes (phpcbf run + verify)
- Security escaping: 2-3 hours (manual review + testing)
- Naming refactoring: 4-6 hours (can defer to M3 cleanup)
- Code quality cleanup: 1-2 hours

## Full Report

See `dev-notes/phpcs-baseline.txt` for complete violation list (7400+ lines)
