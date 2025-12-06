# Contributing to PS Chat

Thank you for considering contributing to PS Chat! This document provides guidelines and procedures for contributing.

## Code of Conduct

Be respectful and inclusive. We're building a community-driven open source project.

## Getting Started

### Prerequisites
- PHP 7.4+ (target: 8.0+)
- WordPress 5.0+
- Composer (for development dependencies)
- Git

### Local Development Setup

```bash
# Clone the repository
git clone https://github.com/cp-psource/ps-chat.git
cd ps-chat

# Install development dependencies
composer install

# Initialize pre-commit hooks (optional but recommended)
cp .githooks/pre-commit .git/hooks/pre-commit
chmod +x .git/hooks/pre-commit
```

## Development Workflow

### Branch Strategy

- **main**: Stable, production-ready code
- **develop**: Integration branch for features
- **feature/\***: New features (e.g., `feature/advanced-moderation`)
- **bugfix/\***: Bug fixes (e.g., `bugfix/avatar-cache-issue`)
- **docs/\***: Documentation improvements

### Creating a Feature/Bugfix

```bash
# Create feature branch from develop
git checkout develop
git pull origin develop
git checkout -b feature/your-feature-name

# Make changes, commit with clear messages
git commit -m "Add: Brief feature description"
# or
git commit -m "Fix: Brief bug description"
```

### Commit Message Format

```
Type: Brief description (50 chars max)

Optional detailed explanation. Wrap at 72 characters.
Reference issues: Fixes #123
```

**Types**: `Add`, `Fix`, `Refactor`, `Docs`, `Tests`, `Perf`, `Security`

## Code Quality Standards

### PHP Standards
- **PHPStan Level 8**: Run before committing
  ```bash
  composer run analyze
  ```
- **PHPCS (WordPress standard)**: Auto-fix with
  ```bash
  composer run lint:fix
  ```
- **Type hints**: Add `@param` and `@return` types to new functions
- **Avoid `global`**: Use dependency injection where possible
- **Security**: Always sanitize input with `sanitize_*()` functions

### JavaScript Standards
- **ESLint**: Modern JS patterns (no `var`, use `const`/`let`)
  ```bash
  npm run lint -- --fix
  ```
- **Comments**: Add JSDoc for public functions

### Documentation
- Update `docs/HOOKS-REFERENCE.md` if adding new filters/actions
- Add inline comments for complex logic
- Update README if changing user-facing functionality

## Testing

### Writing Tests
- **Unit tests**: Test isolated functions (e.g., URL extraction in Media class)
- **Integration tests**: Test message flow through filters
- **Location**: Create `tests/` folder with structure matching `includes/`

```bash
composer run test
```

### Test Coverage
Target: 70%+ coverage for critical paths (AJAX, Media, Avatar)

## Submitting Changes

### Before Push
```bash
# Run full quality suite
composer run quality

# Run security checks
git ls-files | xargs grep -l "password\|secret\|api_key"
```

### Pull Request Process

1. **Push** to your feature branch
2. **Create PR** on GitHub with:
   - Clear title and description
   - Reference any related issues (e.g., "Fixes #123")
   - Checklist:
     ```markdown
     - [ ] PHPStan passes (composer run analyze)
     - [ ] PHPCS passes (composer run lint)
     - [ ] Tests added/updated
     - [ ] Documentation updated
     - [ ] No hardcoded secrets or credentials
     ```

3. **CI/CD Checks**: GitHub Actions must pass
4. **Code Review**: Maintainers will review and provide feedback
5. **Merge**: Squash and merge to develop, then integrate to main in release cycle

## Architecture & Patterns

### Adding Message Filters
Instead of modifying `PSOURCE_Chat::chat_session_enqueue_message()`:

```php
// Use filters (preferred)
add_filter('psource_chat_before_save_message', function($msg, $session) {
    // Transform message before DB save
    return $msg;
}, 10, 2);
```

### Extending AJAX
Add routes in `PSource_Chat_AJAX::register_rest_routes()`:

```php
register_rest_route('psource-chat/v1', '/my-endpoint', [
    'methods' => 'POST',
    'callback' => 'my_callback',
    'permission_callback' => 'is_user_logged_in'
]);
```

### Media Support
Use `PSource_Chat_Media::process_message_media()` hooks for URL detection.

## Documentation Standards

### Inline Comments
```php
// Use for "why", not "what" (code should be self-documenting)
// ✅ Correct: Transient cache to avoid repeated oEmbed API calls
// ❌ Avoid: Get the transient
$data = get_transient('key');
```

### DocBlocks
```php
/**
 * Sends a message to a chat session
 *
 * @param string $session_id    The chat session identifier
 * @param string $message       Sanitized message content
 * @param array  $extra_data    Optional metadata (user_id, etc.)
 *
 * @return array {
 *     @type bool   $success Message sent successfully
 *     @type string $message_id Generated message ID
 * }
 * @throws Exception If session doesn't exist
 */
public function send_message(string $session_id, string $message, array $extra_data = []): array {
```

## Modernization Initiatives

### Quick Wins (Help appreciated!)
- [ ] Convert `var $property` → `public $property` in PSOURCE_Chat
- [ ] Add type hints to `class-psource-chat-media.php` methods
- [ ] Create `PSource_Chat_Container` for dependency injection

### Next Phase
- [ ] PHP 8.x typed properties and return types
- [ ] Interface-based architecture for testability
- [ ] Namespace migration (src/Chat/, src/Media/)

## Performance Considerations

- **Database**: Use `wpdb->prepare()` to prevent SQL injection
- **Caching**: Use WordPress transients for expensive operations (oEmbed, link previews)
- **AJAX Polling**: Configurable intervals; avoid < 3 second polls
- **Media**: Cache detection results in static arrays

## Security Guidelines

1. **Nonces**: Always verify with `wp_verify_nonce()` in AJAX handlers
2. **Input Sanitization**: `sanitize_text_field()`, `sanitize_url()`, `wp_kses_post()`
3. **Output Escaping**: `esc_html()`, `esc_attr()`, `esc_url()`
4. **SQL Queries**: Use `$wpdb->prepare()` always
5. **Admin Only**: Check `current_user_can()` for admin features

## Reporting Issues

Use GitHub Issues with:
- **Clear title**: "Media preview not loading for HTTPS URLs"
- **Reproduction steps**: Step-by-step to recreate
- **Environment**: WordPress version, PHP version, plugins active
- **Screenshots/logs**: Attach if relevant

## Questions or Need Help?

- **Docs**: https://cp-psource.github.io/ps-chat/
- **Issues**: https://github.com/cp-psource/ps-chat/issues
- **Discussions**: https://github.com/cp-psource/ps-chat/discussions

---

**Happy contributing! 🎉**
