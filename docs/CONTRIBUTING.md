# Contributing to PiperPrivacy

## Development Workflow

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Write/update tests
5. Run tests and linting
6. Submit a pull request

## Code Standards

- Follow WordPress Coding Standards
- Use PHP 8.0+ features appropriately
- Document all functions and classes
- Write unit tests for new features

## Module Development

Each module should follow the standard structure:
```
module-name/
  ├── class-module.php           // Main module class
  ├── templates/                 // Module-specific templates
  ├── assets/                    // Module-specific assets
  ├── includes/                  // Module-specific includes
  │   ├── class-controller.php   // Business logic
  │   ├── class-model.php        // Data handling
  │   └── class-view.php         // Display logic
  └── tests/                     // Module-specific tests
```

## Testing

1. Run PHPUnit tests:
```bash
composer test
```

2. Run E2E tests:
```bash
npm run test:e2e
```

## Documentation

- Document all public APIs
- Update README.md for major changes
- Add PHPDoc blocks for classes and methods
- Update CHANGELOG.md

## Commit Messages

Follow conventional commits:
- feat: New feature
- fix: Bug fix
- docs: Documentation
- test: Testing
- refactor: Code refactoring
- style: Code style
- chore: Maintenance
