# Phase 2: PHP 8.x Migration - Action Plan

**Version**: 1.0  
**Status**: Ready to Execute  
**Duration**: 4 weeks  
**Target Release**: v2.6.0

## Overview

Phase 2 focuses on modernizing codebase to PHP 8.x patterns while maintaining backwards compatibility with PHP 7.4+.

### Key Metrics
- **733 `array()` instances** → Need conversion to `[]`
- **0 `var $property`** → Already modern (good!)
- **~100 methods** → Need type hints
- **Codebase**: 10K LOC (high impact area: `class-psource-chat.php` with 295 array() calls)

### Success Criteria
- ✅ All `array()` converted to `[]`
- ✅ Core AJAX methods have type hints
- ✅ PHPStan < 10 exceptions (from 50+)
- ✅ PHPCS passes on priority files
- ✅ No security warnings

---

## Week 1-2: Quick Wins (Safe, High-Value)

### Win 1: Array Syntax Modernization (4-6 hours)
Convert all `array()` → `[]` using regex + validation

**Files (priority order):**
1. `includes/class-psource-chat-media.php` (19 instances)
2. `includes/class-psource-chat-emoji.php` (22 instances)
3. `includes/class-psource-chat-upload.php` (33 instances)
4. `includes/class-psource-chat-ajax.php` (36 instances)
5. `includes/class-psource-chat.php` (295 instances) - DO LAST

**Strategy:**
- Use sed/regex replacements on smaller files first
- Test with `composer run analyze` after each file
- Large files (chat.php) → manual review + regex

**Command Pattern:**
```bash
# Backup first
cp filename filename.backup

# Replace array() with []
sed -i "s/array(/[/g; s/);$/];/g" filename
# BUT: This is naive - need to handle nested arrays!
```

**Better Approach:**
Use PHP-based tool or manual replacement with context review

---

### Win 2: Type Hints on High-Impact Classes (5-7 hours)

Target classes that are called frequently or handle critical logic:

#### Priority 1: Media Class (High Impact)
File: `includes/class-psource-chat-media.php`

```php
// CURRENT (Line 56)
public static function process_message_media( $message, $chat_session ) {

// TARGET
/**
 * Process message and extract media URLs
 * @param string $message The message content
 * @param array $chat_session Session data
 * @return string Processed message
 */
public static function process_message_media( string $message, array $chat_session ): string {
```

Methods to update (4 core methods):
1. `process_message_media()` - string, array → string
2. `render_message_media()` - string, array → string
3. `extract_urls()` - string → array
4. `analyze_url()` - string → array|false

Effort: 1-2 hours per class

#### Priority 2: Avatar Class
File: `includes/class-psource-chat-avatar.php`

Methods:
1. `get_avatar()` - int, int, bool, array → string
2. `resolve_avatar_source()` - int, array → string|false

Effort: 1 hour

#### Priority 3: Upload Class
File: `includes/class-psource-chat-upload.php`

Methods:
1. `handle_upload()` - array → array
2. `validate_file()` - string, array → bool

Effort: 1 hour

---

### Win 3: Security Hardening - AJAX (2-3 hours)

File: `includes/class-psource-chat-ajax.php`

**Current Pattern (Legacy):**
```php
public static function rest_send_message( $request ) {
    $session_id = $request->get_param( 'session_id' );
    $message = $request->get_param( 'message' );
    global $psource_chat;
```

**Target Pattern (Secure):**
```php
/**
 * Send message via REST API
 * @param WP_REST_Request $request The request object
 * @return array|WP_Error Response data
 */
public static function rest_send_message( WP_REST_Request $request ): array {
    check_ajax_referer( 'psource_chat_nonce' );
    
    $session_id = sanitize_key( $request->get_param( 'session_id' ) );
    $message = sanitize_textarea_field( $request->get_param( 'message' ) );
    
    if ( empty( $session_id ) || empty( $message ) ) {
        return array( 'success' => false, 'error' => 'Invalid input' );
    }
```

Focus areas:
1. Add `WP_REST_Request` type hint
2. Nonce verification at method start
3. Sanitization with correct functions
4. Return type hints (array|WP_Error)

---

## Week 3: Main Effort - Large File Refactoring

### Win 4: Core PSOURCE_Chat Class (8-10 hours)

File: `includes/class-psource-chat.php` (7171 lines, 295 array() calls)

**Strategy:**
- Can't do wholesale regex replace (too risky)
- Batch by sections (init, hooks, message handling, etc.)
- Test `composer run quality` after each section

**Sections (in order):**

1. **Constructor + Init Methods** (Lines 50-200)
   - Convert `array()` → `[]` in property initialization
   - Add type hints to `__construct()`
   
2. **Message Handling Methods** (Lines 4800-5200)
   - `chat_session_enqueue_message()` - 40+ array() calls
   - Convert + add type hints
   
3. **Session Management** (Lines 3000-3500)
   - `chat_session_add()`, `chat_session_get()`
   - Add type hints
   
4. **Utility Methods** (Lines 6000-7171)
   - Remaining `array()` conversions

**After Each Section:**
```bash
composer run analyze  # Verify no new errors
composer run lint     # Check style
```

---

## Week 4: Testing & Release

### Phase 4a: Validation (2-3 hours)

```bash
# Full quality check
composer run quality

# Address any warnings/errors
composer run lint:fix
composer run analyze

# Manual testing
- Test basic chat in WordPress
- Verify AJAX endpoints work
- Check Media detection still works
- Test BuddyPress integration (if available)
```

### Phase 4b: Documentation (1 hour)

- Update CHANGELOG.md with "Modernized to PHP 8.x syntax"
- Document any breaking changes (should be none)
- Add to DEVELOPER-GUIDE.md: "This version targets PHP 7.4+, uses modern [] syntax"

### Phase 4c: Release (1 hour)

```bash
# Tag for release
git tag -a v2.6.0 -m "Phase 2: PHP 8.x Modernization (array syntax, type hints, security hardening)"

# Push and test GitHub Actions
git push origin v2.6.0

# GitHub Actions will:
# - Run PHPStan, PHPCS, PHPUnit
# - Create release package (if workflow configured)
```

---

## Implementation Checklist

### Week 1
- [ ] **Day 1**: Backup all files, setup git branch `feature/phase2-modernization`
- [ ] **Day 2-3**: Array syntax on Media class + test
- [ ] **Day 4-5**: Array syntax on Emoji, Upload, AJAX + test
- [ ] **Day 5**: Type hints on Media class

### Week 2
- [ ] **Day 1-2**: Type hints on Avatar + Upload classes
- [ ] **Day 3-4**: Security hardening in AJAX class
- [ ] **Day 5**: Code review + first quality check

### Week 3
- [ ] **Day 1-3**: Core PSOURCE_Chat class - Constructor & Init
- [ ] **Day 4-5**: Core PSOURCE_Chat class - Message Handling

### Week 4
- [ ] **Day 1-2**: Core PSOURCE_Chat class - Session Management + Utilities
- [ ] **Day 3-4**: Full validation (composer run quality)
- [ ] **Day 5**: Documentation + Release v2.6.0

---

## Tools & Commands

### For Batch Replacements
```bash
# Safely replace array() with [] in a file
# 1. Backup
cp file.php file.php.backup

# 2. Preview changes
grep "array(" file.php | head -20

# 3. Use sed (CAREFUL - test first!)
sed -i 's/array(\([^)]*\))/[\1]/g' file.php

# 4. Verify
git diff file.php

# 5. Run quality check
composer run analyze includes/class-psource-chat-media.php
```

### For Type Hints
```bash
# After adding type hints, validate with PHPStan
composer run analyze includes/class-psource-chat-media.php

# Fix style issues
composer run lint:fix includes/class-psource-chat-media.php
```

### For Testing
```bash
# Run tests
composer run test

# Check specific file
composer run analyze includes/class-psource-chat-ajax.php
```

---

## Risk Mitigation

### High Risk: Large array() replacements
**Mitigation:**
- Always backup before sed
- Test with `composer run analyze` immediately after
- Use git to review changes: `git diff filename`
- Revert if needed: `git checkout filename`

### Medium Risk: Breaking type hints
**Mitigation:**
- Add type hints only to parameters/return, not during property init
- Use `@param` + `@return` in DocBlocks first
- Incrementally add PHP type declarations

### Low Risk: Performance
**Mitigation:**
- `[]` is identical performance to `array()`
- Type hints have zero runtime cost
- Database queries unchanged

---

## Success Metrics After Phase 2

| Metric | Target | Current | Expected |
|--------|--------|---------|----------|
| PHPStan Level | 8 (strict) | N/A | Pass with < 10 exceptions |
| PHPCS Standard | WordPress | N/A | 95%+ pass rate |
| array() instances | 0 | 733 | 0 |
| Type hints on core classes | 100% | 0% | 100% |
| Test coverage | 70%+ | TBD | ⏳ Next phase |
| Backwards compat | PHP 7.4+ | Yes | Yes ✅ |

---

## What NOT to Change in Phase 2

- Database schema (unchanged)
- Admin UI/UX (unchanged)
- User-facing features (unchanged)
- API contracts (backwards compatible)
- File/folder structure (stays as-is, namespace migration is Phase 3)

---

## Phase 3 Preview (Q2 2026)

What Phase 2 enables for Phase 3:
- Strong typing → Easier interface extraction
- Modern syntax → Ready for namespace migration
- Security hardened → Safe for advanced features

---

## Questions Before Starting?

1. Want to start with smaller files first (Media, Emoji)?
2. Should we do ALL array() → [] or focus on critical files only?
3. Prefer manual type hint review or should I be aggressive?
4. Any files we should specifically avoid (legacy/experimental)?

**Ready to execute? Start with:** 
```bash
git checkout -b feature/phase2-modernization
composer install
composer run quality  # Baseline
```

---

**Next Step**: Confirm approach and I'll start Week 1! 🚀
