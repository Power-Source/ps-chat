# PS Chat Modernization Roadmap

**Version**: 1.0  
**Last Updated**: December 2025  
**Status**: Phase 1 (Developer Tooling) - COMPLETE ✅

## Executive Summary

PS Chat is a mature WordPress plugin (~10K LOC) with solid architecture but aging codebase patterns. This roadmap guides modernization while maintaining backwards compatibility and ensuring stable releases.

## Phase 1: Developer Infrastructure ✅ COMPLETE

**Goal**: Enable AI agents and human developers to maintain code quality.

### Completed
- [x] **PHPStan Integration** (static analysis, Level 8)
  - Config: `.phpstanrc.neon`
  - Catches: Type mismatches, undefined variables, deprecated calls
  - CI: Runs on PHP 7.4, 8.0, 8.1

- [x] **PHPCS Integration** (code style)
  - Config: `.phpcsrc.xml` (WordPress + PSR-2)
  - Enforces: Property visibility, spacing, naming conventions
  - Auto-fix: `composer run lint:fix`

- [x] **GitHub Actions CI/CD** (`.github/workflows/quality.yml`)
  - Static analysis, code style, tests
  - Runs on push/PR
  - Multi-PHP version testing (7.4, 8.0, 8.1)

- [x] **Composer Setup** (`composer.json`)
  - Dependency management
  - Dev tools as requirements
  - Scripts: `lint`, `analyze`, `test`, `quality`

- [x] **Documentation**
  - `DEVELOPER-SETUP.md` - Quick start for developers
  - `CONTRIBUTING.md` - Contribution workflow + guidelines
  - `.editorconfig` - IDE standardization
  - `.gitignore` - Proper version control

### Success Metrics (Phase 1)
- ✅ `composer install` works
- ✅ `composer run quality` passes
- ✅ GitHub Actions CI/CD operational
- ✅ No developers blocked by tooling setup

---

## Phase 2: PHP 8.x Migration (Q1 2026)

**Goal**: Modernize code to PHP 8.0+ patterns while maintaining 7.0 compatibility.

### Quick Wins (Low Risk)
1. **Property Declarations** (~50 instances)
   ```php
   // Before
   var $chat_sessions = array();
   var $_admin_panels;
   
   // After
   public array $chat_sessions = [];
   private $_admin_panels;
   ```
   - Files: `includes/class-psource-chat.php`, other core classes
   - Effort: 2-4 hours
   - Impact: PHPStan validation, PSR-2 compliance

2. **Type Hints on Methods** (~100 methods)
   - Start with: Media class, Avatar class, Upload class
   - Add `@param` + `@return` types to DocBlocks
   - Run PHPStan after each file to validate
   - Effort: 3-5 days
   - Impact: Better IDE support, fewer runtime errors

3. **Array Syntax**
   - `array()` → `[]` throughout codebase
   - Effort: 1-2 hours (regex replacements)
   - Impact: Modern PHP conventions

### Medium Effort
4. **Sanitization & Nonce Patterns**
   ```php
   // Before
   $user_id = $_GET['user_id'] ?? 0;
   
   // After
   check_admin_referer('nonce_action');
   $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
   ```
   - Files: AJAX handlers in `class-psource-chat-ajax.php`
   - Effort: 2-3 days
   - Impact: Security hardening

5. **Reduce `global` Usage**
   ```php
   // Before (in static methods)
   public static function send_message($msg) {
       global $psource_chat;
       $psource_chat->enqueue_message($msg);
   }
   
   // After (inject dependency)
   public static function send_message($msg, PSOURCE_Chat $chat) {
       $chat->enqueue_message($msg);
   }
   ```
   - Start with: Static AJAX methods
   - Effort: 4-5 days
   - Impact: Testability, dependency clarity

### Phase 2 Timeline
- Weeks 1-2: Property declarations + array syntax (easy wins)
- Weeks 3-4: Type hints on high-risk classes (Media, Avatar)
- Weeks 5-6: AJAX handlers sanitization
- Week 7-8: Testing + release (v2.6.0)

### Success Criteria
- [ ] All properties use `public`/`private`/`protected`
- [ ] Core AJAX methods have type hints
- [ ] PHPStan Level 8 passes with < 10 known exceptions
- [ ] No new security warnings in PHPCS
- [ ] Backwards compatible with PHP 7.4

---

## Phase 3: Architecture Improvements (Q2 2026)

**Goal**: Enable modern testing and cleaner dependency management.

### 3.1 Interfaces & Contracts
Create interfaces for testability:
```php
// src/Contracts/ChatSessionInterface.php
interface ChatSessionInterface {
    public function add_message(string $message): bool;
    public function get_messages(int $limit): array;
}

// src/Chat/Session.php
class Session implements ChatSessionInterface { ... }
```

Benefits:
- Mock objects for unit testing
- Type-safe dependency injection
- Cleaner separation of concerns

Files to create: ~5-7 interfaces  
Effort: 3-4 days

### 3.2 Namespace Migration (PSR-4)
Move from `includes/class-*.php` to PSR-4 namespace:

```
Before:
  includes/class-psource-chat.php
  includes/class-psource-chat-media.php
  
After:
  src/Chat/Core.php
  src/Chat/Media/Handler.php
  src/Chat/Media/Detector.php
```

Benefits:
- Autoloading via Composer
- Better IDE support
- Future-proof for modern WordPress plugins

Effort: 5-7 days (large refactor)  
Risk: Moderate (requires testing all entry points)

### 3.3 Dependency Injection Container
```php
// src/Container.php
class Container {
    private MediaHandler $media;
    private AvatarResolver $avatar;
    private EmojiSystem $emoji;
    
    public function __construct() {
        $this->media = new MediaHandler();
        $this->avatar = new AvatarResolver();
        $this->emoji = new EmojiSystem();
    }
}

// Usage in hooks
add_action('plugins_loaded', function() {
    $container = new Container();
    $container->bootstrap();
});
```

Benefits:
- Single initialization point
- Easier to mock for tests
- Cleaner dependency graph

Effort: 2-3 days  
Risk: Low (can be added incrementally)

### Phase 3 Timeline
- Weeks 1-2: Create interfaces for Media, Avatar, Upload
- Weeks 3-4: Dependency injection container implementation
- Weeks 5-6: Namespace migration (src/ structure)
- Week 7-8: Extensive testing + release (v3.0.0)

---

## Phase 4: Advanced Features (Q3-Q4 2026)

**Conditional on Phase 3 success**

### 4.1 Enhanced Moderation Tools
- Moderation dashboard (separate UI)
- Message approval workflow
- User suspension/ban interface
- Audit logging

Effort: 8-10 days  
Benefit: Better community management

### 4.2 Offline Support (PWA Basics)
- Service Worker for message caching
- Offline queue (messages stored locally)
- Sync on reconnect

Effort: 5-7 days  
Benefit: Better UX, reduced server load

### 4.3 Advanced Notifications
- In-app notification center
- Desktop notifications (Push API)
- Email digest options

Effort: 4-6 days  
Benefit: Better engagement

### 4.4 i18n (Multi-language) Integration
- WordPress .po file infrastructure
- Translation API integration (optional)
- RTL language support

Effort: 3-4 days  
Benefit: Global accessibility

---

## Out of Scope (Not Happening in This Roadmap)

These are technically possible but require separate infrastructure:

| Feature | Why Not Now | Alternative |
|---------|------------|-------------|
| **WebSocket Real-time** | Needs separate Node.js server + reverse proxy | AJAX polling with 3s intervals is adequate |
| **Native Mobile App** | Requires iOS/Android development + store distribution | Use responsive web design + PWA |
| **Video/Voice Chat** | Requires WebRTC signaling server + bandwidth | YouTube/media embeds cover most use cases |
| **Peer-to-peer P2P** | Complex network topology, NAT traversal | Centralized WordPress server is simpler |

---

## Breaking Changes & Version Strategy

### Semantic Versioning
- **v2.x** → v2.6, v2.7, ... (Phase 2, backwards compatible)
- **v3.0** → Namespace migration (breaking, requires code update in child plugins)
- **v4.0** → Potential architecture redesign (future)

### Migration Guide for Extension Developers
When moving to v3.0, extensions need:
```php
// Old (v2.x)
require_once( plugin_dir_path(__FILE__) . '/includes/class-psource-chat.php' );
$chat = new PSOURCE_Chat();

// New (v3.0+)
require_once( plugin_dir_path(__FILE__) . '/vendor/autoload.php' );
use PSource\Chat\Core;
$chat = new Core();
```

---

## Estimated Timeline & Effort

| Phase | Timeline | Effort | Risk |
|-------|----------|--------|------|
| Phase 1: Tooling | Done ✅ | 20 hours | Low |
| Phase 2: PHP 8.x | Q1 2026 (4 weeks) | 40-50 hours | Low |
| Phase 3: Architecture | Q2 2026 (4-5 weeks) | 60-80 hours | Medium |
| Phase 4: Features | Q3-Q4 2026 | 30-50 hours | Medium |
| **Total** | **~5 months** | **~180 hours** | **Low-Medium** |

---

## Key Success Indicators

- [ ] PHPStan Level 8 passes (with documented exceptions)
- [ ] PHPCS passes on 95%+ of files
- [ ] Test coverage > 70% for critical paths (Media, AJAX, Avatar)
- [ ] GitHub Actions CI/CD 100% pass rate on main
- [ ] Zero security issues in annual audit
- [ ] New code only uses modern patterns
- [ ] No regressions in user-facing functionality
- [ ] Community contributions streamlined (clear guidelines)

---

## Contributing to This Roadmap

Help wanted! Priority areas:

1. **Phase 2 Type Hints** - Add `@param`/`@return` to methods
2. **Unit Tests** - Write tests for Media URL extraction, Avatar resolution
3. **Documentation** - Update hook references, add code examples
4. **CSS Cleanup** - Modernize admin CSS (remove old IE support)

See `CONTRIBUTING.md` for how to get started.

---

**Questions?** Open an issue on GitHub or start a discussion!
