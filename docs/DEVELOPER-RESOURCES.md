# Developer Resources

This directory contains everything you need to contribute to PS Chat.

## 📋 Quick Links

### For New Contributors
1. **Start here**: [DEVELOPER-SETUP.md](../DEVELOPER-SETUP.md) - Get your environment working in 5 minutes
2. **Contribution workflow**: [CONTRIBUTING.md](../CONTRIBUTING.md) - Branch strategy, commit messages, PR process
3. **AI agents**: [.github/copilot-instructions.md](../.github/copilot-instructions.md) - Architecture & patterns

### For Maintainers
- **Code quality**: `.github/workflows/quality.yml` - Automated testing on every push
- **Releases**: `.github/workflows/release.yml` - Tag-based release automation
- **Configuration**: 
  - `.phpstanrc.neon` - Static analysis rules
  - `.phpcsrc.xml` - Code style enforcement
  - `.editorconfig` - IDE standardization

### Technical Documentation
- **Architecture**: [DEVELOPER-GUIDE.md](./DEVELOPER-GUIDE.md) - Core components & data flow
- **Hooks & Filters**: [HOOKS-REFERENCE.md](./HOOKS-REFERENCE.md) - Available extension points
- **Modernization**: [MODERNIZATION-ROADMAP.md](./MODERNIZATION-ROADMAP.md) - PHP 8.x upgrade plan

## 🚀 Quick Commands

```bash
# Install development tools
composer install

# Run all quality checks
composer run quality

# Just fix code style
composer run lint:fix

# Run static analysis only
composer run analyze
```

## ✅ Status

| Tool | Status | Details |
|------|--------|---------|
| **PHPStan** | ✅ Ready | Level 8, PHP 7.4-8.1 |
| **PHPCS** | ✅ Ready | WordPress + PSR-2 standards |
| **GitHub Actions** | ✅ Ready | Runs on every push/PR |
| **Unit Tests** | ⏳ Optional | Framework ready, test coverage needed |
| **ESLint** | ⏳ Optional | Config available, needs setup |

## 📖 What's Changed?

### New Files
- `composer.json` - PHP dependency management
- `.phpstanrc.neon` - Static analysis configuration
- `.phpcsrc.xml` - Code style rules
- `.editorconfig` - IDE configuration
- `.github/workflows/quality.yml` - CI/CD pipeline
- `.github/workflows/release.yml` - Release automation
- `CONTRIBUTING.md` - Contribution guidelines
- `DEVELOPER-SETUP.md` - Developer onboarding
- `docs/MODERNIZATION-ROADMAP.md` - Future improvements

### Unchanged
- All existing plugin functionality
- Database tables & API contracts
- Admin interfaces & user-facing features

## 🎯 Next Steps

1. **Install tooling**: `composer install`
2. **Run quality check**: `composer run quality`
3. **Read CONTRIBUTING.md** for workflow details
4. **Start contributing!** Pick an issue or improvement from the roadmap

---

**Questions?** Check [CONTRIBUTING.md](../CONTRIBUTING.md#getting-help) for support links.
