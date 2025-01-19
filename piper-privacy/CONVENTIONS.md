# PiperPrivacy Plugin Conventions

## Directory Structure

```
piper-privacy/
├── admin/                     # Admin-specific functionality
│   ├── css/                  # Admin CSS files
│   ├── js/                   # Admin JavaScript files
│   └── partials/             # Admin view templates
├── includes/                  # Core plugin files
│   ├── core/                 # Core functionality classes
│   ├── helpers/              # Helper functions and utilities
│   ├── interfaces/           # Interface definitions
│   ├── models/               # Data models
│   └── post-types/           # Custom post type definitions
├── languages/                # Translation files
└── public/                   # Public-facing functionality
    ├── css/                  # Public CSS files
    ├── js/                   # Public JavaScript files
    └── partials/             # Public view templates
```

## File Naming Conventions

1. **PHP Class Files**
   - Format: `class-{name}.php`
   - Use hyphens for word separation in filenames
   - Example: `class-privacy-collection.php`

2. **Interface Files**
   - Format: `interface-{name}.php`
   - Example: `interface-data-processor.php`

3. **Partial Template Files**
   - Format: `{name}-display.php` or `{name}-template.php`
   - Example: `admin-display.php`, `collection-template.php`

4. **Asset Files**
   - CSS: `piper-privacy-{context}.css`
   - JavaScript: `piper-privacy-{context}.js`
   - Example: `piper-privacy-admin.css`, `piper-privacy-public.js`

## Namespace Conventions

1. **Base Namespace**
   ```php
   PiperPrivacy
   ```

2. **Core Components**
   ```php
   PiperPrivacy\Core
   PiperPrivacy\Interfaces
   PiperPrivacy\Models
   ```

3. **Post Types**
   ```php
   PiperPrivacy\Post_Types
   ```

4. **Admin and Public**
   ```php
   PiperPrivacy\Admin
   PiperPrivacy\Public
   ```

## Class Naming Conventions

1. **Core Classes**
   - Use PascalCase
   - Example: `Privacy_Collection`, `Data_Processor`

2. **Post Type Classes**
   - Format: `Privacy_{Type}`
   - Example: `Privacy_Collection`, `Privacy_Impact`, `Privacy_Threshold`

3. **Interface Names**
   - Prefix with 'I' for clarity
   - Example: `IData_Processor`, `IExportable`

## Constants

1. **Plugin Constants**
   ```php
   PIPER_PRIVACY_VERSION
   PIPER_PRIVACY_DIR
   PIPER_PRIVACY_URL
   ```

## Post Type Slugs

1. **Custom Post Types**
   ```php
   privacy_collection
   privacy_impact
   privacy_threshold
   ```

2. **Taxonomies**
   ```php
   privacy_collection_status
   privacy_impact_status
   privacy_threshold_status
   ```

## Function Naming

1. **Hook Callbacks**
   - Format: `piper_privacy_{action}_{context}`
   - Example: `piper_privacy_register_post_types`

2. **Helper Functions**
   - Format: `piper_privacy_{action}`
   - Example: `piper_privacy_get_collection_status`

## CSS Classes

1. **Admin Classes**
   - Prefix: `piper-privacy-`
   - Example: `piper-privacy-card`, `piper-privacy-stats`

2. **Public Classes**
   - Prefix: `pp-`
   - Example: `pp-collection`, `pp-impact`

## Database Tables

1. **Table Names**
   - Prefix: `{$wpdb->prefix}piper_privacy_`
   - Example: `wp_piper_privacy_audit_log`

## Important Notes

1. **File Headers**
   - All PHP files should include proper DocBlock headers
   - Include package and subpackage information
   - Example:
     ```php
     /**
      * Privacy Collection Post Type
      *
      * @package    PiperPrivacy
      * @subpackage PiperPrivacy/includes/post-types
      */
     ```

2. **Autoloader**
   - Follows PSR-4 style naming
   - Maps namespace paths to directory structure
   - Handles special cases for post types and admin/public classes

3. **WordPress Coding Standards**
   - Follow WordPress PHP Coding Standards
   - Use WordPress functions and constants where available
   - Proper sanitization and escaping of data

4. **Version Control**
   - Use semantic versioning (MAJOR.MINOR.PATCH)
   - Update version number in main plugin file and readme.txt

## Development Guidelines

1. **Adding New Features**
   - Create new files in appropriate directories
   - Follow naming conventions strictly
   - Update autoloader if adding new namespace paths
   - Add proper documentation and inline comments

2. **Security**
   - Use WordPress nonces for forms
   - Sanitize inputs and escape outputs
   - Follow WordPress security best practices

3. **Internationalization**
   - Use WordPress i18n functions
   - Include text domain in all strings
   - Create/update POT files in languages directory
