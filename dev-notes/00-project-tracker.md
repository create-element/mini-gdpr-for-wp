# Mini WP GDPR - Project Tracker

**Version:** 2.0.0 (Refactor)  
**Last Updated:** 17 February 2026 (14:30)  
**Current Phase:** Milestone 5 (Enhanced Consent Management)  
**Overall Progress:** 87%

---

## Overview

**Project Goal:** Modernize Mini WP GDPR plugin with improved GDPR compliance, better tracker management, enhanced UX, and modern coding standards.

**Key Objectives:**
1. ✅ Remove pp-core.php dependency (move to archive for reference)
2. ✅ Implement proper delay-loading for all trackers (especially Facebook Pixel)
3. ✅ Add "Reject" button to consent popup (comply with GDPR requirements)
4. ✅ Modernize codebase with WordPress Coding Standards
5. ✅ Improve security and implement best practices
6. ✅ Maintain backward compatibility with existing settings (seamless upgrade)
7. ✅ Consider Consent API integration for future-proof implementation

**Constraints:**
- Must preserve all existing `wp_options` keys (no breaking changes for users)
- Settings from v1.4.3 should work seamlessly in v2.0.0
- No data migration required for end users

**Milestone Sequencing Rationale:**
- Milestones 1-2: Set up tooling, establish PHPCS standards
- Milestones 3-6: Core refactoring & feature development (use PHPCS/phpcbf throughout)
- Milestones 7-8: PHPStan analysis, security audit, and manual testing (after bulk of code is stable)
- Milestones 9-10: Document and ship
- PHPCS is used throughout all milestones; PHPStan is saved until the plugin is essentially working in its new form

---

## Active TODO Items

### Milestone 2 Complete ✅
- [x] Create phpcs.xml configuration file
- [x] Create .editorconfig for consistent coding style
- [x] Create composer.json with dev dependencies (PHPCS only, no PHPUnit)
- [x] Run initial PHPCS scan and create baseline (exclude pp-core.php) ✅
- [x] Create shell scripts for code quality checks (check.sh, fix.sh)
- [x] Remove PHPUnit from project
- [x] Document development workflow ✅ (2026-02-17 — rewritten to reflect actual tooling)

### In Progress (Milestone 3 — Remove pp-core.php)
- [x] Create native `Component` base class (`includes/class-component.php`) — tested ✅
- [x] Create native `Settings_Core` class (`includes/class-settings-core.php`) — loaded ✅
- [x] Create admin UI helper functions (`includes/functions-admin-ui.php`) — tested ✅
- [x] Updated `mini-wp-gdpr.php` to load new files in place of pp-core.php

### Next Up (Milestone 3)
- [x] Archive pp-core.php and pp-assets/ to dev-notes/archive/ ✅ (2026-02-17)
- [x] Update all classes extending Component to use new base class ✅
- [x] Complete settings page integration using WordPress Settings API ✅ (2026-02-17)
- [x] Run PHPCS fix pass on legacy files (functions-private.php, class-plugin.php) ✅
- [x] PHPCS fix pass on functions.php (public API) + phpcs.xml prefix update ✅ (2026-02-17)
- [x] PHPCS fix pass on admin templates (cookie-consent-settings.php, etc.) ✅ (2026-02-17)

### In Progress (Milestone 4 — JavaScript Modernisation)
- [x] Phase 4.1 complete ✅ — ES6+ refactoring of mini-gdpr.js, mini-gdpr-admin.js, mini-gdpr-cookie-popup.js; jQuery removed from deps; MiniGdprPopup class with .init(); ARIA attributes added (2026-02-17)
- [x] Phase 4.2 complete ✅ — mini-gdpr-admin-cf7.js rewritten as ES6+ MiniGdprCf7 class; jQuery dep removed; event delegation on tbody; fetch API; popup Escape handler + accept in-flight guard (2026-02-17)
- [x] Phase 4.3 complete ✅ — keyboard Tab traps in consent popup and overlay; focus auto-set to Accept on show; focus returns to more-info on overlay close; aria-describedby on popup; aria-label on close btn (2026-02-17)
- [x] Phase 4.4 complete ✅ — build process (package.json + bin/build.js + terser), all 4 .min.js assets (52-67% reduction), SCRIPT_DEBUG conditional loading, admin scripts in footer (2026-02-17)

---

## Milestones

### Milestone 1: Foundation & Planning ✅ IN PROGRESS
**Target:** Week 1 (Feb 16-23, 2026)  
**Status:** 20% Complete  
**Priority:** Critical

#### Objectives
- [x] Create comprehensive documentation (README.md, CHANGELOG.md)
- [x] Set up Git repository with proper configuration
- [x] Create .gitignore for WordPress plugin development
- [x] Document current state and dependencies
- [x] Create archive directory structure for future use
- [ ] Review pp-core.php functionality and extract necessary components
- [ ] Create detailed specifications for Milestone 2-6
- [ ] Set up development workflows (code standards, testing)

#### Deliverables
- [x] README.md with comprehensive documentation
- [x] CHANGELOG.md with version history
- [x] .gitignore configured for WordPress development
- [x] Project tracker with preliminary milestones
- [ ] pp-core.php functionality audit document
- [ ] Component extraction plan
- [ ] phpcs.xml for WordPress Coding Standards
- [ ] composer.json with development dependencies

#### Notes
- pp-core.php is 2305 lines - needs careful review before removal
- Current file includes Settings_Core class, Component class, and utility functions
- Need to identify what's actively used vs. what can be discarded
- **Important:** pp-core.php and pp-assets/ remain ACTIVE until Milestone 3
- Archive directory created as placeholder for future archival

---

### Milestone 2: Code Standards & Quality Tools (PHPCS)
**Target:** Week 2 (Feb 24 - Mar 2, 2026)  
**Status:** 🟢 Complete  
**Priority:** High

#### Objectives
- [x] ~~Install PHP_CodeSniffer globally with WordPress Coding Standards~~ (Already installed)
- [x] Create phpcs.xml configuration file
- [x] Create .editorconfig for consistent coding style
- [x] Run initial PHPCS scan and create baseline (**exclude pp-core.php** — it's being removed in M3)
- [x] Create simple shell scripts for code quality checks (check.sh, fix.sh)
- [x] Remove PHPUnit from composer.json (not needed for this plugin)
- [x] Document development workflow ✅ (2026-02-17)

#### Deliverables
- [x] phpcs.xml configured and tested
- [x] .editorconfig file
- [x] Initial code quality baseline report (excluding pp-core.php)
- [x] Shell scripts for code quality checks (check.sh, fix.sh)
- [x] Updated dev-notes/workflows/development-workflow.md ✅ (2026-02-17)

#### Tasks
1. ✅ ~~Install PHPCS and WPCS globally~~ (Already available)
2. ✅ Create phpcs.xml configuration file (exclude pp-core.php from scans)
3. ✅ Run initial PHPCS scan and document violations
4. ✅ Create .editorconfig for IDE consistency
5. ✅ Create simple shell scripts for checking/fixing code
6. ✅ Remove PHPUnit dependency (PHPCS + manual testing is sufficient)
7. ✅ Document workflow in dev-notes/ (2026-02-17)

#### Notes
- PHPCS baseline **excludes pp-core.php and pp-assets/** since they're being removed in M3
- Use `phpcs` and `phpcbf` throughout all refactoring milestones (M3-M6)
- PHPStan deferred to M8 — run it once the plugin is essentially working in its new form
- Manual testing performed on westfield.local dev site

#### Success Criteria
- All existing code (excluding pp-core) passes PHPCS (or documented exceptions)
- Shell scripts work for checking/fixing code
- Development workflow documented and tested
- No external dependencies in production code

---

### Milestone 3: Remove pp-core.php Dependency
**Target:** Week 3-4 (Mar 3-16, 2026)  
**Status:** 🟢 Complete  
**Priority:** Critical

#### Objectives
- [ ] Archive pp-core.php and pp-assets/ to dev-notes/archive/
- [ ] Extract and rewrite Settings_Core functionality
- [ ] Extract and rewrite Component base class functionality
- [ ] Implement native WordPress Settings API
- [ ] Create new admin page without pp-core dependencies
- [ ] Migrate utility functions to plugin-specific helpers
- [ ] Test all settings page functionality

#### Sub-Tasks

##### Phase 3.1: Audit & Archive
- [ ] Document all pp-core.php classes and functions
- [ ] **ONLY AFTER REPLACEMENTS TESTED:** Move pp-core.php to dev-notes/archive/
- [ ] **ONLY AFTER REPLACEMENTS TESTED:** Move pp-assets/ to dev-notes/archive
- [ ] Move pp-core.php to dev-notes/archive/pp-core-v1.4.3.php
- [ ] Move pp-assets/ to dev-notes/archive/pp-assets/

##### Phase 3.2: Settings System Rebuild
- [ ] Create new Settings class using WordPress Settings API
- [ ] Implement add_settings_section() and add_settings_field()
- [ ] Rebuild admin settings page templates
- [ ] Preserve all existing option keys (backward compatibility)
- [ ] Add settings validation and sanitization
- [ ] Test settings save/load functionality

##### Phase 3.3: Base Component Rewrite
- [x] Review Component class functionality
- [x] Create minimal base class or remove if unnecessary
- [x] Update all classes extending Component — WPCS style, full PHPDoc ✅
- [ ] Implement lazy loading where beneficial
- [ ] Test class initialization and dependencies

##### Phase 3.4: Utility Function Migration
- [x] Extract utility functions from pp-core.php
- [x] Move to functions-private.php or create new helpers file
- [ ] Rename functions with plugin prefix
- [ ] Update all function calls throughout codebase
- [ ] Test functionality

#### Deliverables
- [ ] pp-core.php removed from active codebase
- [ ] New Settings implementation using WordPress Settings API
- [ ] All settings functionality preserved
- [ ] Backward-compatible option storage
- [ ] Documentation of changes

#### Success Criteria
- Plugin works without pp-core.php
- All settings page features functional
- No breaking changes for existing users
- All existing wp_options keys still work
- Admin UI matches or improves on previous design

---

### Milestone 4: JavaScript Modernization
**Target:** Week 5 (Mar 17-23, 2026)  
**Status:** 🟢 Complete  
**Priority:** High

> **Why now?** Milestones 5 and 6 involve writing significant new JavaScript (consent popup logic, tracker queuing, delay-loading). Modernizing JS *before* those milestones means all new feature code is written in ES6+ from the start, avoiding a rewrite later.

#### Objectives
- [ ] Refactor JavaScript to ES6+ standards
- [ ] Remove jQuery dependencies where possible
- [ ] Implement modern event handling
- [ ] Add proper error handling
- [ ] Optimize asset loading
- [ ] Improve accessibility
- [ ] Add JavaScript documentation

#### Sub-Tasks

##### Phase 4.1: ES6+ Refactoring ✅ Complete
- [x] Convert to ES6 syntax (const/let, arrow functions)
- [x] Use modern DOM API instead of jQuery
- [x] Implement Promise-based AJAX calls (fetch API)
- [x] Add proper scope management (IIFE, classes)
- [x] Use template literals for string building
- [x] Implement modules if beneficial — not beneficial for this plugin size; IIFE/class pattern sufficient

##### Phase 4.2: Event Handling Improvements ✅ Complete (2026-02-17)
- [x] Use addEventListener consistently
- [x] Implement event delegation where appropriate (tbody delegation in MiniGdprCf7)
- [x] Add proper event cleanup (overlay Escape keydown listener removed on close)
- [x] Handle edge cases gracefully (in-flight accept guard, backdrop click guard)
- [x] Add debouncing/throttling where needed (not required — no scroll/resize handlers)

##### Phase 4.3: Accessibility Enhancements ✅ Complete (2026-02-17)
- [x] Add ARIA labels to interactive elements (role, aria-modal, aria-label, aria-live added in Phase 4.1 popup rewrite)
- [x] Implement keyboard navigation (Tab trap in consent popup; Tab trap in overlay panel)
- [x] Ensure focus management (auto-focus Accept on popup open; focus returns to more-info btn on overlay close)
- [x] Add screen reader support (aria-describedby on popup, aria-label on close button)
- [ ] Test with accessibility tools (deferred to M8 QA milestone — dedicated accessibility audit)

##### Phase 4.4: Build & Optimization
- [x] Set up build process — package.json + bin/build.js (Terser) ✅ (2026-02-17)
- [x] Minify and bundle JavaScript — 4 .min.js assets committed (52-67% reduction) ✅ (2026-02-17)
- [x] Create source maps — generated by build, excluded from git via .gitignore ✅ (2026-02-17)
- [x] Optimize asset loading strategy — SCRIPT_DEBUG conditional loading; admin scripts moved to footer ✅ (2026-02-17)
- [x] Test performance improvements — plugin active, error log clean, all 4 .min.js serve HTTP 200, front-end HTML loading .min.js, no debug.log errors ✅ (2026-02-17)

#### Deliverables
- [ ] Modernized JavaScript codebase
- [ ] Reduced/eliminated jQuery dependency
- [ ] Improved accessibility
- [ ] Build process documentation
- [ ] Performance benchmarks

#### Success Criteria
- JavaScript follows modern best practices
- No jQuery dependency (or minimal)
- Accessible to keyboard and screen reader users
- Performance improved or maintained
- Code is maintainable and documented

---

### Milestone 5: Enhanced Consent Management
**Target:** Week 6-7 (Mar 24 - Apr 6, 2026)  
**Status:** Not Started  
**Priority:** Critical

#### Objectives
- [ ] Add "Reject" button to consent popup
- [ ] Implement proper rejection handling
- [ ] Store rejection consent alongside acceptance
- [ ] Update popup UI/UX for clarity
- [ ] Add settings for rejection behavior
- [ ] Implement Consent API integration (if feasible)
- [ ] Update JavaScript for new button logic

#### Sub-Tasks

##### Phase 5.1: Popup UI Enhancement ✅ Complete (2026-02-17)
- [x] Design new popup layout with 3 buttons: Reject, Info, Accept
- [x] Update mini-gdpr-cookie-popup.css for new layout (responsive flex-wrap at 18em)
- [x] Ensure responsive design on all screen sizes
- [x] Add accessibility improvements (aria-label on all 3 buttons; shared hasStoredDecision helper)
- [x] Create settings UI for customizable button text (constants + register_settings done; admin UI added in cookie-consent-settings.php) ✅ (2026-02-17)

##### Phase 5.2: Rejection Logic Implementation ✅ Complete (2026-02-17)
- [x] Create `mgwRejectScripts()` JavaScript function
- [x] Store rejection status in localStorage/cookie
- [x] Prevent blocked scripts from loading on rejection
- [x] Add "change preferences" mechanism for rejected users
- [x] Update consent duration to apply to rejections too

##### Phase 5.3: Consent API Integration Research ✅ Complete (2026-02-17)
- [x] Research browser Consent API compatibility
- [x] Evaluate feasibility for Facebook Pixel, GA, etc.
- [x] Create proof-of-concept implementation — Google Consent Mode v2 implemented; FB Pixel deferred to M6
- [x] Document findings and recommendations — dev-notes/consent-api-research.md
- [x] Implement if beneficial, document limitations if not — Google Consent Mode v2 live; IAB TCF out of scope documented

##### Phase 5.4: Backend Consent Tracking
- [x] Update database schema for rejection tracking — user meta sufficient, no custom table needed ✅ (2026-02-17)
- [x] Store rejection consent in user meta (for logged-in users) — reject_via_ajax() + META_REJECTED_GDPR_WHEN ✅ (2026-02-17)
- [x] Add admin UI to view consent/rejection statistics — consent-stats.php template + stat card CSS + render_settings_page() hook ✅ (2026-02-17)
- [x] Create filters for consent/rejection events — mwg_consent_accepted + mwg_consent_rejected action hooks ✅ (2026-02-17)
- [ ] Update WooCommerce integration for rejection handling (deferred)

#### Deliverables
- [ ] Updated popup with Reject/Info/Accept buttons
- [ ] Rejection handling in JavaScript
- [ ] Backend rejection tracking
- [ ] Consent API integration (or documented analysis)
- [ ] Updated admin UI for consent management
- [ ] Documentation for developers

#### Success Criteria
- Users can explicitly reject tracking
- Rejection is properly stored and respected
- No tracking scripts load after rejection
- Users can change their decision later
- Accessible and user-friendly UI

---

### Milestone 6: Advanced Tracker Delay-Loading
**Target:** Week 8-9 (Apr 7-20, 2026)  
**Status:** 🟡 In Progress  
**Priority:** High

#### Objectives
- [ ] Implement proper delay-loading for Facebook Pixel
- [ ] Improve delay-loading for Google Analytics
- [ ] Add delay-loading for Microsoft Clarity
- [ ] Create unified tracker injection system
- [ ] Ensure scripts only load after explicit consent
- [ ] Optimize performance (no blocking, async loading)
- [ ] Test with various tracking configurations

#### Sub-Tasks

##### Phase 6.1: Facebook Pixel Enhancement ✅ Complete (2026-02-17)
- [x] Research Facebook Pixel delayed initialization methods — consent-api-research.md
- [x] Implement queue system for FB events before consent — fbq stub + fbq('consent','revoke') guard
- [x] Load FB Pixel script only after consent — loadFacebookPixel() called on consent/returning visit
- [x] Replay queued events after script loads — fbevents.js processes fbq.queue automatically
- [x] FB Pixel Consent API: fbq('consent','grant') queued in loadFacebookPixel() before SDK loads ✅ (2026-02-17)
- [x] Test pixel functionality with delayed loading ✅ (2026-02-17)
- [x] Document Facebook Pixel delay-loading approach — dev-notes/tracker-delay-loading.md

##### Phase 6.2: Google Analytics Enhancement
- [ ] Review current GA implementation
- [ ] Implement gtag.js delay-loading
- [ ] Queue analytics events before consent
- [ ] Optimize gtag.js loading strategy
- [ ] Test with GA4 and Universal Analytics
- [ ] Ensure accurate event tracking

##### Phase 6.3: Microsoft Clarity Enhancement
- [ ] Implement delayed Clarity injection
- [ ] Test session recording with delayed load
- [ ] Ensure heatmap data accuracy
- [ ] Document Clarity-specific considerations

##### Phase 6.4: Generic Tracker Framework
- [ ] Create abstraction layer for tracker management
- [ ] Implement queue system for all trackers
- [ ] Add support for custom third-party trackers
- [ ] Create developer API for adding new trackers
- [ ] Document tracker registration process
- [ ] Add examples for common tracking tools

#### Deliverables
- [ ] Enhanced Facebook Pixel delay-loading
- [ ] Improved Google Analytics injection
- [ ] Better Microsoft Clarity handling
- [ ] Generic tracker framework
- [ ] Developer documentation for custom trackers
- [ ] Performance benchmarks

#### Success Criteria
- All trackers load only after consent
- No performance degradation on page load
- Events properly queued and replayed
- Works with major tracking platforms
- Extensible for custom trackers

---

### Milestone 7: Security Audit & Best Practices
**Target:** Week 10 (Apr 21-27, 2026)  
**Status:** Not Started  
**Priority:** High

> **Note:** Security best practices should be followed throughout all milestones. This milestone is a dedicated verification pass over all new and existing code to catch anything that was missed.

#### Objectives
- [ ] Comprehensive security audit of all code (including M3-M6 changes)
- [ ] Strengthen nonce verification
- [ ] Review all sanitization and escaping
- [ ] Implement capability checks consistently
- [ ] Update AJAX handlers with security best practices
- [ ] Add rate limiting for AJAX endpoints
- [ ] Document security measures

#### Sub-Tasks

##### Phase 7.1: Input Sanitization Audit
- [ ] Review all $_POST, $_GET, $_REQUEST usage
- [ ] Ensure proper sanitization functions used
- [ ] Verify wp_unslash() where needed
- [ ] Check file upload handling (if any)
- [ ] Test with malicious input

##### Phase 7.2: Output Escaping Audit
- [ ] Review all echo/print statements
- [ ] Ensure proper escaping (esc_html, esc_attr, esc_url)
- [ ] Check template files for proper escaping
- [ ] Verify JavaScript variable output
- [ ] Test for XSS vulnerabilities

##### Phase 7.3: AJAX & Nonce Security
- [ ] Review all AJAX handlers
- [ ] Verify nonce creation and verification
- [ ] Check capability requirements
- [ ] Implement rate limiting for sensitive actions
- [ ] Test CSRF prevention

##### Phase 7.4: Database Security
- [ ] Review all database queries
- [ ] Verify $wpdb->prepare() usage
- [ ] Check for SQL injection vulnerabilities
- [ ] Ensure proper data validation
- [ ] Test with malicious SQL input

#### Deliverables
- [ ] Security audit report
- [ ] Updated code with security improvements
- [ ] Rate limiting implementation
- [ ] Security documentation
- [ ] Penetration testing results

#### Success Criteria
- No critical security vulnerabilities
- All inputs properly sanitized
- All outputs properly escaped
- AJAX endpoints secured with nonces
- Database queries use prepared statements

---

### Milestone 8: PHPStan Analysis, Manual Testing & Quality Assurance
**Target:** Week 11-12 (Apr 28 - May 11, 2026)  
**Status:** Not Started  
**Priority:** High

> **Note:** PHPStan is run here — after the bulk of refactoring (M3-M6) is complete and the plugin is essentially working. This avoids chasing PHPStan errors on code that's still being rewritten. PHPCS/phpcbf are used throughout earlier milestones.

#### Objectives
- [ ] Configure PHPStan (phpstan.neon) and run initial scan
- [ ] Establish PHPStan baseline and fix critical issues
- [ ] Manual functional testing of all features
- [ ] WooCommerce integration testing
- [ ] Contact Form 7 integration testing
- [ ] Browser compatibility testing
- [ ] Performance testing
- [ ] Accessibility testing

#### Sub-Tasks

##### Phase 8.0: PHPStan Static Analysis
- [ ] Create phpstan.neon configuration file
- [ ] Run initial PHPStan scan at level 5
- [ ] Establish baseline for existing issues
- [ ] Fix critical errors (undefined methods, type mismatches)
- [ ] Progressively increase level if feasible
- [ ] Document any accepted baselines with rationale

##### Phase 8.1: Functional Testing (Manual)
- [ ] Test settings page (save/load all options)
- [ ] Test consent popup display and behavior
- [ ] Test "Accept" button functionality
- [ ] Test "Reject" button functionality
- [ ] Test "Info" modal
- [ ] Test script blocking before consent
- [ ] Test script loading after consent
- [ ] Test consent persistence (localStorage/cookie)
- [ ] Test consent duration expiry

##### Phase 8.2: Integration Tests (Manual)
- [ ] Test WooCommerce checkout flow with consent popup
- [ ] Test WooCommerce MyAccount consent toggle
- [ ] Test Contact Form 7 consent checkbox
- [ ] Test with Google Analytics
- [ ] Test with Facebook Pixel
- [ ] Test with Microsoft Clarity
- [ ] Test AJAX endpoints via browser console

##### Phase 8.3: Browser & Device Testing
- [ ] Test on Chrome, Firefox, Safari, Edge
- [ ] Test on mobile devices (iOS, Android)
- [ ] Test consent popup on various screen sizes
- [ ] Test with JavaScript disabled (graceful degradation)
- [ ] Test with different privacy settings (strict mode, etc.)

##### Phase 8.4: Performance Testing
- [ ] Measure page load impact (before/after plugin)
- [ ] Test with multiple tracking scripts enabled
- [ ] Benchmark AJAX request times
- [ ] Test with high-traffic scenarios (LoadForge/k6)
- [ ] Compare v1.4.3 vs v2.0.0 performance

#### Deliverables
- [ ] phpstan.neon configuration file
- [ ] PHPStan baseline report
- [ ] Manual testing checklist (completed)
- [ ] Browser compatibility report
- [ ] Performance benchmarks
- [ ] Test documentation (known issues, edge cases)

#### Success Criteria
- PHPStan passes at level 5+ with no critical errors
- All features work as expected in manual testing
- Works in all major browsers
- No performance degradation vs v1.4.3
- Accessibility standards met (keyboard nav, screen readers)
- PHPCS passes with 0 errors

---

### Milestone 9: Documentation & Developer Experience
**Target:** Week 13 (May 12-18, 2026)  
**Status:** Not Started  
**Priority:** Medium

#### Objectives
- [ ] Complete inline code documentation
- [ ] Create developer documentation
- [ ] Document all hooks and filters
- [ ] Create code examples
- [ ] Update README with new features
- [ ] Create migration guide from v1.x
- [ ] Generate PHPDoc documentation

#### Sub-Tasks

##### Phase 9.1: Code Documentation
- [ ] Add PHPDoc blocks to all classes
- [ ] Document all public methods
- [ ] Add @since tags for version tracking
- [ ] Document parameters and return types
- [ ] Add usage examples in docblocks

##### Phase 9.2: Developer Documentation
- [ ] Create developer guide in dev-notes/
- [ ] Document all available hooks/filters
- [ ] Create examples for common customizations
- [ ] Document tracker registration API
- [ ] Create troubleshooting guide

##### Phase 9.3: Migration Documentation
- [ ] Create v1.x to v2.0 migration guide
- [ ] Document breaking changes (if any)
- [ ] Create upgrade checklist
- [ ] Document new features
- [ ] Create FAQ for upgrading

##### Phase 9.4: User Documentation
- [ ] Update README.md with v2.0 features
- [ ] Update settings page help text
- [ ] Create user guide
- [ ] Create video tutorials (optional)
- [ ] Update WordPress.org documentation

#### Deliverables
- [ ] Comprehensive inline documentation
- [ ] Developer guide
- [ ] Hook/filter reference
- [ ] Migration guide
- [ ] Updated README.md
- [ ] User documentation

#### Success Criteria
- All public APIs documented
- Developer guide complete and tested
- Migration path clear
- User documentation comprehensive
- No undocumented features

---

### Milestone 10: Release Preparation & Launch
**Target:** Week 14 (May 19-25, 2026)  
**Status:** Not Started  
**Priority:** Critical

#### Objectives
- [ ] Final code review and cleanup
- [ ] Version number updates
- [ ] CHANGELOG.md finalization
- [ ] Create release package
- [ ] WordPress.org submission preparation
- [ ] Beta testing with select users
- [ ] Final security audit
- [ ] Performance optimization

#### Sub-Tasks

##### Phase 10.1: Code Freeze & Review
- [ ] Final PHPCS check and cleanup
- [ ] Final PHPStan scan
- [ ] Code review of all changes
- [ ] Remove debug code and console.log
- [ ] Verify all TODOs resolved
- [ ] Check for unused code

##### Phase 10.2: Version Management
- [ ] Update version to 2.0.0 in all files
- [ ] Update CHANGELOG.md with final changes
- [ ] Update readme.txt for WordPress.org
- [ ] Update README.md
- [ ] Tag version in Git
- [ ] Create GitHub release

##### Phase 10.3: Package Creation
- [ ] Build production assets (minified)
- [ ] Create .zip package
- [ ] Test installation from .zip
- [ ] Verify file structure
- [ ] Test on fresh WordPress install
- [ ] Test upgrade from v1.4.3

##### Phase 10.4: Beta Testing
- [ ] Deploy to test environments
- [ ] Distribute to beta testers
- [ ] Collect feedback
- [ ] Fix critical issues
- [ ] Retest after fixes

##### Phase 10.5: WordPress.org Submission
- [ ] Update plugin assets (banner, icon)
- [ ] Submit to WordPress.org SVN
- [ ] Prepare plugin page content
- [ ] Create screenshots
- [ ] Submit for review

#### Deliverables
- [ ] Mini WP GDPR v2.0.0 release package
- [ ] Updated WordPress.org listing
- [ ] GitHub release with notes
- [ ] Beta testing report
- [ ] Marketing materials

#### Success Criteria
- All tests passing
- No critical bugs
- WordPress.org approved
- Smooth upgrade from v1.4.3
- Documentation complete

---

## Technical Debt

### Current Known Issues
1. **pp-core.php Dependency** - 2305-line framework adds complexity
2. **Facebook Pixel Blocking** - Cannot be deferred until consent (technical limitation in v1.x)
3. **Missing "Reject" Button** - GDPR requires explicit rejection option
4. **jQuery Dependency** - Can modernize to vanilla JavaScript
5. **Limited Testing** - No automated tests currently
6. **No Build Process** - JavaScript not minified/bundled optimally
7. **Accessibility Gaps** - Popup may not be fully accessible

### Future Considerations
- [ ] REST API endpoints for headless WordPress
- [ ] WordPress Block Editor integration (Gutenberg blocks)
- [ ] Multisite support improvements
- [ ] Cookie categorization (necessary vs. optional)
- [ ] Consent API integration once browser support improves
- [ ] Integration with popular cookie consent services
- [ ] Export user consent data (GDPR data portability)

---

## Notes for Development

### Backward Compatibility Strategy

**Critical:** All existing `wp_options` must remain compatible:
- `mwg_is_cookie_consent_popup_enabled`
- `mwg_always_show_consent`
- `mwg_is_ga_enabled`
- `mwg_ga_tracking_code`
- `mwg_is_fbpx_enabled`
- `mwg_fbpx_id`
- `mwg_consent_duration`
- All other options listed in constants.php

**Approach:**
1. New Settings class must use same option keys
2. Add new options with new keys (don't modify existing)
3. Test upgrade from v1.4.3 to ensure seamless transition
4. Include upgrade routine if schema changes needed

### Testing Environments Needed
- [ ] WordPress 6.4 (minimum supported)
- [ ] WordPress 6.9 (current stable)
- [ ] PHP 7.4, 8.0, 8.1, 8.2, 8.3
- [ ] WooCommerce 8.x and 9.x
- [ ] Contact Form 7 latest version
- [ ] Multisite installation (optional but recommended)

### Key Dependencies to Monitor
- WordPress Settings API changes
- WooCommerce HPOS updates
- Contact Form 7 API changes
- Browser Consent API evolution
- GDPR regulation updates

### Performance Targets
- Page load impact: <100ms with popup
- AJAX response time: <500ms
- Script blocking overhead: <50ms
- First Contentful Paint: No degradation vs. v1.4.3

### Security Considerations
- All AJAX endpoints require nonce verification
- All user input must be sanitized
- All database queries must use prepared statements
- All output must be escaped appropriately
- Rate limiting on consent-related AJAX calls
- No PII stored in localStorage without encryption

---

## Progress Tracking

| Milestone | Target Completion | Status | Progress |
|-----------|------------------|--------|----------|
| 1. Foundation & Planning | Feb 23, 2026 | 🟡 In Progress | 20% |
| 2. Code Standards & Quality Tools (PHPCS) | Mar 2, 2026 | 🟢 Complete | 100% |
| 3. Remove pp-core.php | Mar 16, 2026 | 🟢 Complete | 100% |
| 4. JavaScript Modernization | Mar 23, 2026 | 🟢 Complete | 100% |
| 5. Enhanced Consent Management | Apr 6, 2026 | 🟢 Complete | 95% |
| 6. Advanced Tracker Delay-Loading | Apr 20, 2026 | 🟡 In Progress | 25% |
| 7. Security Audit & Best Practices | Apr 27, 2026 | ⚪ Not Started | 0% |
| 8. PHPStan, Testing & QA | May 11, 2026 | ⚪ Not Started | 0% |
| 9. Documentation | May 18, 2026 | ⚪ Not Started | 0% |
| 10. Release Preparation | May 25, 2026 | ⚪ Not Started | 0% |

**Legend:**
- 🟢 Complete
- 🟡 In Progress
- 🔴 Blocked
- ⚪ Not Started

---

## Changelog

| Date | Change | Reason |
|------|--------|--------|
| 2026-02-16 | Initial project tracker created | — |
| 2026-02-16 | Reordered milestones: JS Modernization moved from M7→M4; Consent Management M4→M5; Tracker Delay-Loading M5→M6; Security M6→M7 | JS modernization must happen before feature milestones that write significant new JS. Avoids writing ES5/jQuery code then immediately rewriting it. |
| 2026-02-16 | M2 expanded to include PHPUnit test infrastructure setup | Testing infra should be available from M3 onwards so tests are written alongside code, not bolted on at the end. |
| 2026-02-16 | M2 PHPCS baseline now explicitly excludes pp-core.php | No point baselining 2305 lines of code that's being removed in M3. |
| 2026-02-16 | M8 refocused as coverage gap-filling and integration/browser testing | With test infra in M2 and tests written per-milestone, M8 becomes verification rather than "write all the tests." |
| 2026-02-16 | Removed PHPUnit from composer.json and vendor | Plugin too simple for unit tests — PHPCS + PHPStan + manual testing is sufficient |
| 2026-02-16 | PHPStan deferred from M2 to M8 | Run PHPStan after bulk refactoring (M3-M6) is complete; no point chasing static analysis errors on code being rewritten. PHPCS used throughout. |
| 2026-02-16 | M2 renamed "Code Standards & Quality Tools (PHPCS)" | Reflects PHPCS-only focus; PHPStan moved to M8 |
| 2026-02-17 | M3 In Progress: Component, Settings_Core, functions-admin-ui.php created | Native classes replace pp-core.php foundation; all pass PHPCS; plugin loads cleanly |
| 2026-02-17 | M3 PHPCS pass on functions-private.php and class-plugin.php | Global var prefixes, hook name prefixes, wp_unslash(), SESE phpcs:disable blocks; plugin active and loads cleanly |
| 2026-02-17 | M3 PHPCS pass on all Component-extending classes | class-admin-hooks, class-public-hooks, class-user-controller, class-cf7-helper, class-script-blocker converted to WPCS style with full PHPDoc; removed unused properties, proper escaping |
| 2026-02-17 | M3 Archive pp-core.php and pp-assets/ | Moved to dev-notes/archive/ via git mv; added assets/mwg-admin.css as plugin-native admin CSS replacing pp-assets/pp-admin.css; updated pp_enqueue_admin_assets() reference; removed stale phpcs.xml exclusions |
| 2026-02-17 | M3 Testing sprint passed | Plugin active, error log clean, front-end 200, no debug.log errors; archive tasks verified |
| 2026-02-17 | M3 Settings class WPCS + WordPress Settings API registration | Removed duplicate admin_menu hook from Settings constructor; added register_settings() registering all 18 options via register_setting(); Plugin::admin_init() now calls register_settings() unconditionally; fixed json_encode→wp_json_encode; pixel ID sanitisation wp_kses_post→sanitize_text_field |
| 2026-02-17 | M2 Complete: development-workflow.md rewritten | Removed references to removed tooling (PHPUnit, bin/*.sh, composer/vendor); documented actual workflow using global phpcs/phpcbf; added manual testing protocol |
| 2026-02-17 | M3 PHPCS fixes: functions.php + phpcs.xml | functions.php converted to WPCS style (tabs, docblocks, K&R braces); phpcs.xml updated to add mwg prefix to allowed list (public API functions use mwg_ prefix) |
| 2026-02-17 | M3 Testing sprint passed (functions.php PHPCS fix) | Plugin active, error log clean, front-end 200, PHPCS clean on functions.php; 1 task remaining in M3: PHPCS fix pass on admin templates |
| 2026-02-17 | M3 Complete — Testing sprint passed (admin templates PHPCS fix) | Plugin active, settings page renders cleanly, cookie consent popup visible on front-end, error log empty, no debug.log errors; M3 fully complete — moving to M4 (JavaScript Modernisation) |
| 2026-02-17 | M4 Phase 4.1 (partial) coding sprint: mini-gdpr.js and mini-gdpr-admin.js converted to ES6+ | Replaced jQuery/var with ES6 classes, const/let, arrow functions, async/await, fetch API; removed jQuery from enqueue deps; PHPCS clean on PHP files; testing sprint passed — front-end 200, error log clean, no debug.log |
| 2026-02-17 | M4 Phase 4.1 (continued): mini-gdpr-cookie-popup.js converted to ES6+ | Wrapped in IIFE; global functions → MiniGdprPopup class; var → const/let; fixed DOMContentLoaded-on-button bug (→ click); removed dead createElement('extra') block; added ARIA (role, aria-modal, aria-label, aria-live); added backdrop click guard; removed all console.log; classList.add instead of className +=; hasOwnProperty guards on for...in loops |
| 2026-02-17 | M4 Phase 4.1 complete — testing sprint passed | Plugin active, error log clean, front-end 200, mgwcsData output correct, .init() call verified; Phase 4.1 all tasks marked complete; ARIA label task in Phase 4.3 also marked done; next sprint: Phase 4.2 (Event Handling Improvements) |
| 2026-02-17 | M4 Phase 4.2 complete — testing sprint passed | Plugin active, error log clean, front-end/admin 200, no debug.log; MiniGdprCf7 class verified (ES6+, event delegation, fetch API); cookie popup Escape handler and accept in-flight guard verified; jQuery dep dropped from class-admin-hooks.php; Phase 4.2 sub-tasks all marked complete |
| 2026-02-17 | M4 Phase 4.3 complete — testing sprint passed | Plugin active, error log clean, front-end 200, no debug.log; Tab trap in consent popup (accept↔more-info) and overlay (within panel) verified in code; focus auto-set to Accept on popup show; focus returns to more-info btn on overlay close; aria-describedby on popup, aria-label on close btn |
| 2026-02-17 | M4 Phase 4.4 coding sprint: build process + minification + asset loading | Added package.json (terser dev dep) + bin/build.js; minified all 4 JS assets (52-67% reduction); SCRIPT_DEBUG conditional enqueue; admin scripts moved to footer; development-workflow.md updated with build docs |
| 2026-02-17 | M4 Phase 4.4 testing sprint passed — M4 Complete | Plugin active, error log clean, all 4 .min.js serve HTTP 200, front-end HTML confirmed loading .min.js, no debug.log errors; Milestone 4 (JavaScript Modernisation) fully complete |
| 2026-02-17 | M5 Phase 5.1 coding sprint — Reject button added to consent popup | [Reject] [info...] [Accept] 3-button layout; rejectConsent() + hasRejected() + hasStoredDecision() in JS; rejection cookie name (rcn) added to mgwcsData; OPT_CONSENT_ACCEPT/REJECT/INFO_BTN_TEXT constants + register_settings(); responsive CSS; minified assets rebuilt |
| 2026-02-17 | M5 Phase 5.1 testing sprint passed | Plugin active, error log clean, front-end 200, mgwcsData contains rcn + rjt + mre, all 3 JS methods verified, responsive CSS confirmed; Phase 5.1 complete — moving to Phase 5.2 (Rejection Logic) |
| 2026-02-17 | M5 Phase 5.2 coding sprint — rejection logic + manage-preferences mechanism | rejectConsent() stores rejection in localStorage/cookie (with cd duration), no scripts injected; showManagePreferencesLink() renders floating 🍪 button; changePreferences() clears both decisions, resets accept guard, re-shows popup; init() shows manage btn for returning users (consented or rejected); public API: window.mgwRejectScripts() + window.mgwShowCookiePreferences(); CSS for #mgwMngBtn; minified assets rebuilt |
| 2026-02-17 | M5 Phase 5.2 testing sprint passed | Plugin active, error log clean, front-end 200, no debug.log; mgwcsData contains rcn; all Phase 5.2 symbols in min.js; #mgwMngBtn CSS in served stylesheet; Phase 5.2 fully complete |
| 2026-02-17 | M5 Phase 5.4 coding sprint (partial) — server-side rejection tracking | META_REJECTED_GDPR_WHEN + REJECT_GDPR_ACTION constants; User_Controller: reject_gdpr_terms_now(), has_user_rejected_gdpr(), when_did_user_reject_gdpr(); clear_gdpr_accepted_status() now clears rejection meta too; reject_via_ajax() AJAX handler (wp_ajax_rejectgdpr); mwg_consent_accepted + mwg_consent_rejected developer action hooks; Script_Blocker adds ajaxUrl/rejectAction/rejectNonce to mgwcsData for logged-in users; rejectConsent() fires fire-and-forget AJAX for logged-in users; minified assets rebuilt |
| 2026-02-17 | M5 Phase 5.4 testing sprint passed | Plugin active, error log clean, front-end 200, no debug.log; REJECT_GDPR_ACTION=rejectgdpr constant confirmed; wp_ajax_rejectgdpr hook registered → Plugin::reject_via_ajax; META_REJECTED_GDPR_WHEN=_pwg_rejected_gdpr_when confirmed; Script_Blocker logged-in guard verified in source; JS rejectConsent() fire-and-forget confirmed; Phase 5.4 (partial) complete — deferred: admin stats UI, WooCommerce integration |
| 2026-02-17 | M5 Phase 5.3 coding sprint — Google Consent Mode v2 implemented | OPT_GA_CONSENT_MODE_ENABLED constant + settings registration; tracker-google-analytics.php wp_head (priority 1) outputs dataLayer init + gtag stub + consent defaults when enabled; consentToScripts() fires gtag('consent','update',granted) before insertBlockedScripts(); admin UI checkbox in GA settings section; dev-notes/consent-api-research.md documents all evaluated APIs (GA, FB Pixel, Clarity, IAB TCF, native browser APIs) |
| 2026-02-17 | M5 Phase 5.3 testing sprint passed | Plugin active, error log clean, front-end 200, no debug.log; OPT_GA_CONSENT_MODE_ENABLED constant confirmed in constants.php; wp_head outputs gtag("consent","default",{all denied, wait_for_update:500}) at priority 1 when option enabled; gtag("consent","update",{all granted}) confirmed in .min.js consentToScripts(); admin UI checkbox confirmed in trackers-settings-google.php; consent-api-research.md file verified (9890 bytes); M5 progress updated to 75% |
| 2026-02-17 | M5 Phase 5.1 fully complete — testing sprint passed | Admin UI fields for Accept/Reject/Info button text added to cookie-consent-settings.php; OPT_CONSENT_ACCEPT_TEXT, OPT_CONSENT_REJECT_TEXT, OPT_CONSENT_INFO_BTN_TEXT referenced with esc_attr(); constants confirmed in constants.php; registered in class-settings.php; PHPCS clean; plugin active, error log clean, front-end 200, no debug.log; Phase 5.1 fully complete — M5 progress 85% |
| 2026-02-17 | M5 Phase 5.4 admin consent stats dashboard — testing sprint passed | consent-stats.php template (live $wpdb COUNT queries for total users, accepted, rejected, undecided); stat card CSS (mwg-stat-cards, mwg-stat--accepted/rejected/undecided) in mwg-admin.css; render_settings_page() includes template below form; PHPCS clean on all 3 files; plugin active, error log clean, front-end 200, no debug.log; Phase 5.4 admin stats task complete ✅ |
| 2026-02-17 | M6 Phase 6.1 coding sprint — FB Pixel Consent API revoke/grant signals | tracker-facebook-pixel.php: fbq('consent','revoke') added before fbq('init') in stub (defensive GDPR guard — if fbevents.js loads unexpectedly it starts in revoked state); mini-gdpr-cookie-popup.js + .min.js: fbq('consent','grant') added in loadFacebookPixel() before fbevents.js loads; queue order: revoke→grant→init→PageView; pixel initialises in fully-granted mode; PHPCS clean; minified assets rebuilt |
| 2026-02-17 | M6 Phase 6.1 testing sprint passed — Phase 6.1 Complete | Plugin active, error log clean, front-end 200, no debug.log; fbq('consent','revoke') confirmed in PHP stub (line 122); fbq('consent','grant') confirmed in .min.js loadFacebookPixel(); minified assets verified; all Phase 6.1 tasks marked complete |

---

**Last Updated:** 17 February 2026 (15:10)  
**Next Review:** 23 February 2026  
**Next Action:** Coding sprint — M6 Phase 6.2: Google Analytics Enhancement (review current GA impl, implement gtag.js delay-loading, queue analytics events before consent, test with GA4)
