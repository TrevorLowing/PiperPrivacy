# Contributing to PiperPrivacy

## Getting Started

1. **Fork the Repository**
   - Visit [PiperPrivacy on GitHub](https://github.com/TrevorLowing/PiperPrivacy)
   - Click the "Fork" button
   - Clone your fork locally

2. **Set Up Development Environment**
   - Install prerequisites (WordPress, PHP, MySQL)
   - Install Composer dependencies
   - Configure WordPress development environment
   - Install and activate required plugins

## Development Workflow

### 1. Branching Strategy
- `main` - Production-ready code
- `develop` - Development branch
- Feature branches: `feature/your-feature-name`
- Bug fixes: `fix/bug-description`
- Releases: `release/x.x.x`

### 2. Coding Standards
- Follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)
- Use PHP 8.0+ features appropriately
- Follow PSR-4 autoloading standards
- Maintain backward compatibility

### 3. Documentation
- Update README.md when adding features
- Document all functions and classes
- Update technical documentation
- Include inline code comments
- Update API documentation if needed

### 4. Testing
- Write PHPUnit tests for new features
- Update existing tests as needed
- Test across different WordPress versions
- Verify with different PHP versions
- Check for plugin conflicts

## Pull Request Process

1. **Before Submitting**
   - Update documentation
   - Add/update tests
   - Run code standards checks
   - Test functionality
   - Resolve conflicts with main

2. **PR Guidelines**
   - Clear, descriptive title
   - Detailed description of changes
   - Reference related issues
   - Include testing instructions
   - List breaking changes

3. **Review Process**
   - Address reviewer comments
   - Make requested changes
   - Maintain PR discussion
   - Update based on feedback

## Code Standards

### 1. PHP Coding
```php
// Use type hints
public function processData(array $data): bool {
    // Implementation
}

// Use constants for configuration
const VERSION = '1.0.0';

// Use meaningful names
public function validatePrivacyCollection(): void {
    // Implementation
}
```

### 2. WordPress Integration
```php
// Use WordPress functions
$option = get_option('piper_privacy_setting');

// Proper hook usage
add_action('init', [$this, 'initializePlugin']);

// Security measures
check_ajax_referer('piper_privacy_nonce');
```

### 3. Database
- Use `$wpdb` properly
- Prepare SQL statements
- Use WordPress options API
- Follow naming conventions

## Commit Messages

### Format
```
type(scope): subject

body

footer
```

### Types
- feat: New feature
- fix: Bug fix
- docs: Documentation
- style: Formatting
- refactor: Code restructuring
- test: Testing
- chore: Maintenance

### Examples
```
feat(collections): add privacy collection export feature

- Add PDF export functionality
- Include configurable templates
- Add export permissions

Closes #123
```

## Release Process

1. **Version Bump**
   - Update version in plugin file
   - Update changelog
   - Update dependencies

2. **Testing**
   - Run full test suite
   - Perform manual testing
   - Check compatibility

3. **Documentation**
   - Update version numbers
   - Update feature documentation
   - Check all links

4. **Release**
   - Create release branch
   - Tag new version
   - Update changelog
   - Merge to main

## Questions?

- Open an issue for discussion
- Check existing documentation
- Review closed issues/PRs
- Contact maintainers
