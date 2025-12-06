# Developer Setup & Tooling Guide

## Quick Start

```bash
# 1. Clone and install
git clone https://github.com/cp-psource/ps-chat.git
cd ps-chat

# 2. Install PHP dependencies (development tools)
composer install

# 3. Run quality checks before committing
composer run quality
```

## Available Commands

### Quality Assurance

```bash
# Run all checks (lint + static analysis + tests)
composer run quality

# Run individual checks
composer run lint          # PHPCS code style
composer run lint:fix      # Auto-fix style issues
composer run analyze       # PHPStan static analysis
composer run test          # PHPUnit tests
```

### Git Hooks (Optional)

```bash
# Auto-run quality checks before commit
mkdir -p .git/hooks
cp .githooks/pre-commit .git/hooks/pre-commit
chmod +x .git/hooks/pre-commit
```

## Development Workflow

### Step 1: Create Feature Branch
```bash
git checkout develop
git pull origin develop
git checkout -b feature/my-feature
```

### Step 2: Make Changes
- Edit code in `includes/`, `lib/`, or `js/`
- Follow patterns in [CONTRIBUTING.md](CONTRIBUTING.md)

### Step 3: Test & Lint
```bash
# Auto-fix style issues
composer run lint:fix

# Check for problems
composer run analyze

# Run tests (if available)
composer run test
```

### Step 4: Commit & Push
```bash
git add .
git commit -m "Add: Feature description"
git push origin feature/my-feature
```

### Step 5: Create Pull Request
- Go to https://github.com/cp-psource/ps-chat/pulls
- Create PR against `develop` branch
- GitHub Actions will verify all checks pass

## Tools Overview

### PHPStan (Static Analysis)
Catches type errors and bugs before runtime.

```bash
# Run analysis
vendor/bin/phpstan analyse includes/ lib/

# Config: .phpstanrc.neon
```

**What it catches:**
- Undefined variables
- Type mismatches
- Missing type hints
- Deprecated function calls

### PHPCS (Code Style)
Enforces WordPress + PSR-2 coding standards.

```bash
# Check for issues
vendor/bin/phpcs includes/ lib/ --standard=.phpcsrc.xml

# Auto-fix (many issues)
vendor/bin/phpcbf includes/ lib/ --standard=.phpcsrc.xml
```

**Common fixes:**
- `var $property` → `public $property`
- Spacing and indentation
- Naming conventions

### ESLint (JavaScript)
Modern JavaScript patterns for frontend code.

```bash
npm install
npm run lint         # Check for issues
npm run lint -- --fix  # Auto-fix
```

### PHPUnit (Testing)
Unit and integration tests.

```bash
vendor/bin/phpunit
```

## PHP Version Targeting

### Current Support
- **Minimum**: PHP 7.0 (for backwards compatibility)
- **Recommended**: PHP 8.0+
- **Target**: PHP 8.1+

### Modern Patterns

When implementing new features, use PHP 8 where possible:

```php
// ✅ Use (PHP 8.0+)
public array $sessions = [];
public function send(string $message): void {}

// ❌ Avoid in new code (legacy)
var $sessions = array();
function send($message) {}
```

## Database & Testing

### WordPress Test Suite
For integration testing with WordPress hooks/filters:

```bash
# Initialize WordPress test database
vendor/bin/install-wp-tests.sh wordpress_test root '' localhost latest

# Run integration tests
composer run test
```

## GitHub Actions CI/CD

Automatic checks run on every push/PR:

1. **PHPStan** - Static analysis on PHP 7.4, 8.0, 8.1
2. **PHPCS** - Code style checking
3. **PHPUnit** - Tests against WordPress 5.9, 6.0, 6.5
4. **ESLint** - JavaScript quality (if package.json present)
5. **Security** - Checks for hardcoded secrets

See `.github/workflows/quality.yml` for full pipeline.

## Documentation Standards

### DocBlocks
```php
/**
 * Sends a message to chat session
 *
 * @param string $session_id Chat session ID
 * @param string $message    Message text
 *
 * @return bool Success status
 */
public function send_message(string $session_id, string $message): bool {
```

### Inline Comments
```php
// ✅ Explain WHY
// Cache oEmbed data to avoid repeated API calls on every page load
$oembed = get_transient('oembed_' . $video_id);

// ❌ Don't explain WHAT (code is self-documenting)
// Get oembed
$oembed = get_transient('oembed_' . $video_id);
```

## Modernization Roadmap

### Phase 1: Tooling (Current) ✅
- [x] PHPStan + PHPCS setup
- [x] GitHub Actions CI/CD
- [x] Composer + package management
- [x] Contributing guidelines

### Phase 2: PHP 8.x Migration
- [ ] Type hints on all methods
- [ ] Replace `var $property` with typed properties
- [ ] Match expressions instead of switch statements

### Phase 3: Architecture
- [ ] Dependency Injection container
- [ ] Interface-based design
- [ ] Namespace PSR-4 autoloading

## Common Issues

### "PHPStan errors about WordPress globals"
This is expected - see `.phpstanrc.neon` exceptions. These are documented patterns in WordPress plugins.

### "PHPCS complains about 'var' keyword"
Update to `public $property` (or `private`/`protected`). This is PSR-2 compliance.

### "ESLint wants to run but no package.json"
Optional - skip if not using modern JS. Frontend code uses vanilla JS + jQuery.

## Getting Help

- **CONTRIBUTING.md** - Contribution guidelines
- **docs/DEVELOPER-GUIDE.md** - Architecture deep dive
- **docs/HOOKS-REFERENCE.md** - Available hooks/filters
- **GitHub Issues** - Report bugs

---

**Happy coding! 🚀**
