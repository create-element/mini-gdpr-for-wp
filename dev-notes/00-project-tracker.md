# Mini WP GDPR - Project Tracker

**Version:** 2.0.0 (Refactor)  
**Last Updated:** 16 February 2026  
**Current Phase:** Milestone 1 (Foundation & Planning)  
**Overall Progress:** 5%

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
- Milestones 1-3: Set up tooling, establish standards, then clean house (remove pp-core)
- Milestone 4: Modernize JavaScript *before* writing new feature JS (M5/M6)
- Milestones 5-6: Build new features on a modern foundation
- Milestones 7-8: Verify security and fill testing gaps across all new code
- Milestones 9-10: Document and ship
- Security and testing are baked into every milestone; M7/M8 are verification passes

---

## Active TODO Items

### In Progress (Milestone 2)
- [x] Create phpcs.xml configuration file
- [x] Create .editorconfig for consistent coding style
- [x] Create composer.json with dev dependencies
- [x] Run initial PHPCS scan and create baseline (exclude pp-core.php) ✅
- [x] Set up PHPUnit test infrastructure

### Next Up (Milestone 2)
- [ ] Create simple shell scripts for code quality checks (optional)
- [ ] Document development workflow

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

### Milestone 2: Code Standards & Quality Tools
**Target:** Week 2 (Feb 24 - Mar 2, 2026)  
**Status:** 🟡 In Progress (85% complete)  
**Priority:** High

#### Objectives
- [x] ~~Install PHP_CodeSniffer globally with WordPress Coding Standards~~ (Already installed)
- [x] ~~Configure PHPStan globally~~ (Already installed)
- [x] Create phpcs.xml configuration file
- [x] Create .editorconfig for consistent coding style
- [x] Run initial PHPCS scan and create baseline (**exclude pp-core.php** — it's being removed in M3)
- [x] Create simple shell scripts for code quality checks (check.sh, fix.sh, test.sh)
- [ ] Run PHPStan scan and establish baseline
- [ ] Document development workflow

#### Deliverables
- [x] phpcs.xml configured and tested
- [x] .editorconfig file
- [x] Initial code quality baseline report (excluding pp-core.php)
- [x] Shell scripts for code quality checks (check.sh, fix.sh, test.sh)
- [ ] phpstan.neon configuration file
- [ ] Updated dev-notes/workflows/code-standards.md

#### Tasks
1. ✅ ~~Install PHPCS and WPCS globally~~ (Already available)
2. ✅ Create phpcs.xml configuration file (exclude pp-core.php from scans)
3. ✅ Run initial PHPCS scan and document violations
4. ✅ Create .editorconfig for IDE consistency
5. ✅ Create simple shell scripts for checking/fixing code
6. Run PHPStan scan and establish baseline
7. Document workflow in dev-notes/

#### Notes
- PHPCS baseline **excludes pp-core.php and pp-assets/** since they're being removed in M3
- **No PHPUnit/Composer**: Plugin is simple enough that PHPCS + PHPStan + manual testing is sufficient
- Focus on WordPress Coding Standards compliance and static analysis
- Manual testing performed on westfield.local dev site

#### Success Criteria
- All existing code (excluding pp-core) passes PHPCS (or documented exceptions)
- PHPStan runs without critical errors
- Shell scripts work for checking/fixing code
- Development workflow documented and tested
- No external dependencies in production code

---

### Milestone 3: Remove pp-core.php Dependency
**Target:** Week 3-4 (Mar 3-16, 2026)  
**Status:** Not Started  
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
- [ ] Review Component class functionality
- [ ] Create minimal base class or remove if unnecessary
- [ ] Update all classes extending Component
- [ ] Implement lazy loading where beneficial
- [ ] Test class initialization and dependencies

##### Phase 3.4: Utility Function Migration
- [ ] Extract utility functions from pp-core.php
- [ ] Move to functions-private.php or create new helpers file
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
**Status:** Not Started  
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

##### Phase 4.1: ES6+ Refactoring
- [ ] Convert to ES6 syntax (const/let, arrow functions)
- [ ] Use modern DOM API instead of jQuery
- [ ] Implement Promise-based AJAX calls
- [ ] Add proper scope management
- [ ] Use template literals for string building
- [ ] Implement modules if beneficial

##### Phase 4.2: Event Handling Improvements
- [ ] Use addEventListener consistently
- [ ] Implement event delegation where appropriate
- [ ] Add proper event cleanup
- [ ] Handle edge cases gracefully
- [ ] Add debouncing/throttling where needed

##### Phase 4.3: Accessibility Enhancements
- [ ] Add ARIA labels to interactive elements
- [ ] Implement keyboard navigation
- [ ] Ensure focus management
- [ ] Add screen reader support
- [ ] Test with accessibility tools

##### Phase 4.4: Build & Optimization
- [ ] Set up build process (if needed)
- [ ] Minify and bundle JavaScript
- [ ] Create source maps
- [ ] Optimize asset loading strategy
- [ ] Test performance improvements

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

##### Phase 5.1: Popup UI Enhancement
- [ ] Design new popup layout with 3 buttons: Reject, Info, Accept
- [ ] Update mini-gdpr-cookie-popup.css for new layout
- [ ] Ensure responsive design on all screen sizes
- [ ] Add accessibility improvements (ARIA labels, keyboard navigation)
- [ ] Create option for customizable button text

##### Phase 5.2: Rejection Logic Implementation
- [ ] Create `mgwRejectScripts()` JavaScript function
- [ ] Store rejection status in localStorage/cookie
- [ ] Prevent blocked scripts from loading on rejection
- [ ] Add "change preferences" mechanism for rejected users
- [ ] Update consent duration to apply to rejections too

##### Phase 5.3: Consent API Integration Research
- [ ] Research browser Consent API compatibility
- [ ] Evaluate feasibility for Facebook Pixel, GA, etc.
- [ ] Create proof-of-concept implementation
- [ ] Document findings and recommendations
- [ ] Implement if beneficial, document limitations if not

##### Phase 5.4: Backend Consent Tracking
- [ ] Update database schema for rejection tracking (if needed)
- [ ] Store rejection consent in user meta (for logged-in users)
- [ ] Add admin UI to view consent/rejection statistics
- [ ] Create filters for consent/rejection events
- [ ] Update WooCommerce integration for rejection handling

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
**Status:** Not Started  
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

##### Phase 6.1: Facebook Pixel Enhancement
- [ ] Research Facebook Pixel delayed initialization methods
- [ ] Implement queue system for FB events before consent
- [ ] Load FB Pixel script only after consent
- [ ] Replay queued events after script loads
- [ ] Test pixel functionality with delayed loading
- [ ] Document Facebook Pixel delay-loading approach

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

### Milestone 8: Manual Testing & Quality Assurance
**Target:** Week 11-12 (Apr 28 - May 11, 2026)  
**Status:** Not Started  
**Priority:** High

> **Note:** This plugin uses PHPCS + PHPStan for code quality. Testing focuses on manual functional testing, browser compatibility, and performance verification.

#### Objectives
- [ ] Manual functional testing of all features
- [ ] WooCommerce integration testing
- [ ] Contact Form 7 integration testing
- [ ] Browser compatibility testing
- [ ] Performance testing
- [ ] Accessibility testing

#### Sub-Tasks

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
- [ ] Manual testing checklist (completed)
- [ ] Browser compatibility report
- [ ] Performance benchmarks
- [ ] Test documentation (known issues, edge cases)

#### Success Criteria
- All features work as expected in manual testing
- Works in all major browsers
- No performance degradation vs v1.4.3
- Accessibility standards met (keyboard nav, screen readers)
- PHPCS and PHPStan pass with 0 errors

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
| 2. Code Standards, Quality & Test Infra | Mar 2, 2026 | 🟡 In Progress | 71% |
| 3. Remove pp-core.php | Mar 16, 2026 | ⚪ Not Started | 0% |
| 4. JavaScript Modernization | Mar 23, 2026 | ⚪ Not Started | 0% |
| 5. Enhanced Consent Management | Apr 6, 2026 | ⚪ Not Started | 0% |
| 6. Advanced Tracker Delay-Loading | Apr 20, 2026 | ⚪ Not Started | 0% |
| 7. Security Audit & Best Practices | Apr 27, 2026 | ⚪ Not Started | 0% |
| 8. Testing & QA | May 11, 2026 | ⚪ Not Started | 0% |
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

---

**Last Updated:** 16 February 2026  
**Next Review:** 23 February 2026
