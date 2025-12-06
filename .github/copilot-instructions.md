# PS Chat Plugin - AI Development Guide

## Architecture Overview

**PS Chat** is a self-hosted WordPress chat plugin supporting direct messaging, BuddyPress group chats, and rich media embedding. Key architectural principles:

- **No external dependencies**: All chat processing happens server-side
- **Modular class structure**: Separate concerns for AJAX, media, avatars, emoji via dedicated classes
- **Legacy & modern dual-stack**: Uses both traditional WordPress AJAX (`wp_ajax_chatProcess`) and modern REST API (`PSource_Chat_AJAX`)
- **Database-driven**: Custom tables via `PSOURCE_Chat::tablename()` for messages, sessions, and metadata

### Core Components

| Component | Location | Purpose |
|-----------|----------|---------|
| `PSOURCE_Chat` | `includes/class-psource-chat.php` (7171 lines) | Main plugin orchestrator; manages sessions, options, hooks |
| `PSource_Chat_AJAX` | `includes/class-psource-chat-ajax.php` | Modern REST API + admin-ajax hybrid; message polling/sending |
| `PSource_Chat_Media` | `includes/class-psource-chat-media.php` | Link previews, image/video detection, YouTube embedding |
| `PSource_Chat_Upload` | `includes/class-psource-chat-upload.php` | File uploads, cleanup, media preview rendering |
| `PSource_Chat_Avatar` | `includes/class-psource-chat-avatar.php` | User avatar resolution (WordPress/BuddyPress/PS-Community) |
| `PSource_Chat_Emoji` | `includes/class-psource-chat-emoji.php` | Modern emoji picker system |
| Utilities & Widgets | `lib/` | Admin panels, BuddyPress integration, widget system |

## Data Flow

1. **Message Send**: JavaScript → `wp_ajax_chatProcess` (legacy) or REST `/psource-chat/v1/messages` (modern) → `PSOURCE_Chat::chat_session_enqueue_message()` 
2. **Message Retrieval**: AJAX poll → `PSource_Chat_AJAX::handle_poll_request()` → uses transient cache for performance
3. **Media Processing**: Message saved → `psource_chat_before_save_message` filter → `PSource_Chat_Media::process_message_media()` extracts URLs
4. **Display**: `psource_chat_display_message` filter → `PSource_Chat_Media::render_message_media()` renders embeds

## Development Workflow

### Debugging
- **Enable debug logging**: Uncomment `define('CHAT_DEBUG_LOG', 1);` in main plugin file (before utilities load)
- **Check AJAX system**: Admin panel → Chat Settings → AJAX-System; choose between "PS Chat AJAX" (recommended) or "CMS AJAX"
- **Multisite awareness**: Plugin uses `PSOURCE_Chat::tablename()` which respects `$wpdb->base_prefix` for network-wide tables

### Adding New Features
1. **Hooks first**: Prefer filters/actions in `includes/class-psource-chat.php` before modifying core
2. **Session context**: Chat sessions (array) contain: `session_id`, `session_type` (private|open|bp-group), `created_on`, `user_id`, `invitation_info`
3. **Message structure**: Each message row has: `chat_id`, `chat_message`, `user_id`, `timestamp`, `blog_id`, and custom meta

### Key Hooks & Filters

**Message Processing** (use for content transformation):
- `psource_chat_before_save_message`: Sanitize/transform before DB storage (2 params: message, session)
- `psource_chat_display_message`: Transform before frontend display (2 params: message, message_row)

**User Management**:
- `psource-chat-user-statuses`: Modify available user statuses array
- `psource-chat-options-defaults`: Customize default settings

**AJAX** (legacy):
- `wp_ajax_chatProcess` / `wp_ajax_nopriv_chatProcess`: Legacy chat action handler

## Project Conventions

- **Namespace**: Classes use `PSOURCE_` or `PSource_` prefix; functions use `psource_chat_` prefix
- **Security**: Always use `wp_verify_nonce()` in AJAX, apply `sanitize_*()` / `wp_kses_post()` on user input
- **Internationalization**: All strings wrapped in `__()` / `_e()` with domain `psource-chat`
- **Multisite**: Tables are network-wide; use `get_site_option()` for network settings, `get_option()` for blog-specific
- **Performance**: Media detection uses simple regex; results cached in static `$cache` array within methods

## Integration Patterns

**BuddyPress Integration** (`lib/psource_chat_buddypress.php`):
- Group chats: Detect via `session_type === 'bp-group'`
- Moderation: Check `groups_is_user_mod()` / `groups_is_user_admin()`

**Media Support** (automatic):
- URLs detected during save → stored with metadata
- On display → link previews, image embeds, YouTube players rendered
- Lazy-loaded for performance; cached preview data

**Community Integration** (`lib/psource_chat_cpcommunitie.php`):
- Avatar resolution supports PS Community plugin via custom meta

## File Organization

```
includes/     # Core classes (AJAX, Media, Avatar, Emoji, Upload)
lib/          # Admin panels, utilities, widgets, integrations
js/           # Frontend chat UI, AJAX requests, media handling
css/          # Admin & frontend styles
templates/    # Pop-out chat template (templates/psource-chat-pop-out.php)
docs/         # Developer documentation (DEVELOPER-GUIDE.md, HOOKS-REFERENCE.md)
```

## Common Tasks

**Add a custom message filter**:
```php
add_filter('psource_chat_display_message', function($msg, $row) {
    // Transform $msg; $row contains full message metadata
    return $msg;
}, 10, 2);
```

**Access current session context** (inside PSOURCE_Chat methods):
```php
// $this->chat_sessions[$session_id] contains full session array
// $this->chat_auth has current user: ['user_id', 'auth_hash', 'avatar']
```

**Extend AJAX endpoints** (modern approach):
Add route in `PSource_Chat_AJAX::register_rest_routes()` with validation callbacks

## Developer Tooling & Modernization

### Current State Analysis
- **Codebase**: ~10K LOC (includes/), 249 functions/classes
- **PHP Support**: 7.0+ (legacy patterns present: `var $property`, `global $`, mixed public/private)
- **Setup**: Modern REST API alongside legacy `wp_ajax_chatProcess`; comprehensive tooling now in place

### Setup & Configuration Files
- **DEVELOPER-SETUP.md** - Quick start guide for developers
- **CONTRIBUTING.md** - Contribution guidelines & workflow
- **composer.json** - PHP dependencies (PHPUnit, PHPStan, PHPCS)
- **.phpstanrc.neon** - Static analysis (Level 8, strict typing)
- **.phpcsrc.xml** - Code style (WordPress + PSR-2)
- **.editorconfig** - IDE standardization (tabs, line endings)
- **.github/workflows/quality.yml** - CI/CD: PHPStan, PHPCS, PHPUnit on PHP 7.4-8.1
- **.github/workflows/release.yml** - Release automation

### Recommended Modernization Path

#### Phase 1: Developer Infrastructure (Immediate)
These tools help AI agents AND human developers maintain code quality:

**1. Static Analysis** (catch bugs before runtime)
- **PHPStan** (`composer require --dev phpstan/phpstan`)
  - Level 8 (strict): Type safety, undefined variables
  - Config: Create `.phpstanrc.neon` excluding wp-content, vendor
  - Goal: Eliminate `global $` patterns, improve type hints
  
- **PHP_CodeSniffer** (`composer require --dev squizlabs/php_codesniffer`)
  - Standard: WordPress (with PSR-12 baseline)
  - Enforces: Property declarations (`public`, not `var`), spacing, naming
  - Pre-commit hook: `phpcs includes/ lib/`

**2. Dependency Management**
```bash
# Initialize PHP tooling
composer init --name="cp-psource/ps-chat" --type=wordpress-plugin
composer require --dev phpunit/phpunit phpstan/phpstan squizlabs/php_codesniffer
```

**3. Git Workflows** (create `.github/workflows/`)
```yaml
# .github/workflows/quality.yml
name: Code Quality
on: [push, pull_request]
jobs:
  static-analysis:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: PHPStan
        run: vendor/bin/phpstan analyse includes/ lib/
      - name: PHPCS
        run: vendor/bin/phpcs includes/ lib/
```

#### Phase 2: PHP 8.x Migration (Next Sprint)
Target: PHP 8.0+ (typed properties, match expressions, named arguments)

**Quick Wins**:
- Replace `var $property` → `public $property` (PSR-2 compliance)
- Add typed function parameters to Media, Avatar, Upload classes
- Convert legacy `isset($_GET)` patterns → proper nonce + `sanitize_*()` with type hints

**Example refactor**:
```php
// Before (legacy)
var $chat_sessions = array();
function chat_session_add($session) {
    global $wpdb;
    // ...
}

// After (PHP 8)
public array $chat_sessions = [];
public function chat_session_add(array $session): bool {
    global $wpdb;
    // ...
}
```

#### Phase 3: Architecture Improvements (Later)
- **Dependency Injection**: Replace `global $psource_chat` with constructor injection in AJAX/Media classes
- **Interfaces**: Create `ChatSessionInterface`, `MediaHandlerInterface` for testability
- **Namespace**: Move `includes/class-*` → `src/Chat/Session/`, `src/Media/Handler/`

### Testing Strategy
- **PHPUnit**: Unit tests for Media URL extraction, Avatar resolution logic
- **Integration**: Test message flow through filters (`psource_chat_before_save_message`)
- **config**: `phpunit.xml` in root, test bootstrap loads WordPress

### ESLint for JavaScript (Frontend Quality)
```json
// .eslintrc.json
{
  "env": { "browser": true, "es2021": true },
  "extends": ["eslint:recommended"],
  "rules": {
    "no-var": "error",
    "prefer-const": "error",
    "strict": ["error", "function"]
  }
}
```
Run: `eslint js/ --fix` to auto-fix modern JS patterns in chat UI.

### Actionable Checklist for AI Agents
When implementing features in this codebase:
1. ✅ Add `@param` and `@return` type hints (PHPStan will validate)
2. ✅ Use `$this->property` instead of `var $property` (PSR-2)
3. ✅ Avoid `global $` – pass dependencies as constructor params (where possible)
4. ✅ Sanitize user input with `sanitize_*()`, verify nonces before save
5. ✅ Add hooks/filters instead of modifying core message logic
6. ✅ Test complex logic (URL extraction, emoji handling) with PHPUnit

### Performance Monitoring
- **Query Analysis**: Log slow DB queries via `SAVEQUERIES` constant (enable in debug)
- **Caching Strategy**: Transients already in place; document TTLs in code comments
- **AJAX Polling**: Configurable intervals; avoid hammering `wp_ajax_psource_chat_poll` below 3s

### Documentation for New Developers
- **DEVELOPER-GUIDE.md**: Already exists; add "Getting Started" section with `composer install && vendor/bin/phpstan analyse`
- **CONTRIBUTING.md**: Branch strategy (feature/*, bugfix/*), PR checklist (PHPStan pass, PHPCS pass)
- **.editorconfig**: Standardize indent (tabs vs spaces), line endings across team

---

**Version**: 1.0.0 | **PHP**: 7.0+ (target 8.0+) | **WP**: 4.9+ | **Last Updated**: Dec 2025
