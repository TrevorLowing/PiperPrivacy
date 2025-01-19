# PiperPrivacy Setup Guide

## Prerequisites

1. **WordPress Requirements**
   - WordPress 6.0 or higher
   - PHP 8.0 or higher
   - MySQL 5.7+ or MariaDB 10.3+

2. **Required Plugins**
   - Advanced Custom Fields PRO

## Installation

### Manual Installation
1. Download the latest release from the [releases page](https://github.com/TrevorLowing/PiperPrivacy/releases)
2. Upload the `piper-privacy` folder to `/wp-content/plugins/`
3. Activate the plugin through the WordPress admin panel

### Development Installation
1. Clone the repository:
   ```bash
   git clone https://github.com/TrevorLowing/PiperPrivacy.git
   ```
2. Install dependencies:
   ```bash
   cd PiperPrivacy
   composer install
   ```
3. Symlink or copy the `piper-privacy` folder to your WordPress plugins directory

## Configuration

### 1. Initial Setup
1. Navigate to Settings → PiperPrivacy
2. Configure your organization details
3. Set up default privacy thresholds
4. Configure email notifications

### 2. User Roles
1. Privacy Administrator
   - Full access to all privacy management features
   - Can configure plugin settings
   - Can manage all collections and assessments

2. Privacy Officer
   - Can create and manage privacy collections
   - Can perform assessments
   - Can review and approve submissions

3. Department Manager
   - Can create privacy collections
   - Can submit threshold assessments
   - Limited to own department's records

### 3. Custom Fields
The plugin uses Advanced Custom Fields PRO for:
- Privacy Collection forms
- Threshold Assessment forms
- Impact Assessment forms

These fields are automatically configured during installation.

## Post-Installation Steps

1. **Verify Installation**
   - Check plugin activation status
   - Verify custom post types are registered
   - Confirm ACF fields are created

2. **Configure Workflows**
   - Set up approval processes
   - Configure notification triggers
   - Define assessment criteria

3. **Test Configuration**
   - Create a test privacy collection
   - Run a test threshold assessment
   - Verify email notifications

## Troubleshooting

### Common Issues

1. **Plugin Activation Fails**
   - Verify WordPress version compatibility
   - Check PHP version requirements
   - Confirm ACF PRO is activated

2. **Forms Not Displaying**
   - Clear WordPress cache
   - Verify ACF field groups are properly imported
   - Check for JavaScript console errors

3. **Permission Issues**
   - Review user role assignments
   - Check capability settings
   - Verify WordPress file permissions

### Debug Mode
To enable debug mode:
1. Add to wp-config.php:
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   ```
2. Check `/wp-content/debug.log` for errors

## Support

- [Documentation](https://github.com/TrevorLowing/PiperPrivacy/docs)
- [Issue Tracker](https://github.com/TrevorLowing/PiperPrivacy/issues)
- [Contributing Guidelines](CONTRIBUTING.md)
