# PiperPrivacy - Technical Architecture

## Technology Stack

### Core Framework
- WordPress 6.0+
- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+

### Key Dependencies
1. **Data Management**
   - MetaBox Pro (Custom fields framework)
   - Advanced Custom Fields PRO (Additional field types)
   - MetaBox AIO (All-in-one extension pack)
   - MetaBox - FrontEnd Submission

2. **Form Management**
   - Fluent Forms Pro
   - Fluent Forms PDF
   - Fluent Forms API Integrations

3. **UI/UX Framework**
   - Bricks Builder (Visual Website Builder)
   - Automatic CSS (ACSS) for dynamic styling
   - CSS Custom Properties for theming
   - BEM CSS naming

4. **Workflow Management**
   - FluentBoards
   - FluentCRM (For notifications)
   - WP Cron Manager (For scheduled tasks)

## Plugin Architecture

### 1. Core Structure
```php
piper-privacy/
├── includes/
│   ├── Core/                     # Core WordPress plugin infrastructure
│   │   ├── class-plugin.php      # Main plugin initialization
│   │   ├── class-loader.php      # WordPress hooks/filters manager
│   │   └── class-i18n.php        # Internationalization
│   ├── post-types/              # Custom post type definitions
│   │   ├── class-privacy-collection.php
│   │   ├── class-privacy-threshold.php
│   │   └── class-privacy-impact.php
│   ├── integrations/           # Third-party integrations
│   │   ├── class-acf-integration.php
│   │   ├── class-metabox-integration.php
│   │   ├── class-fluentforms-integration.php
│   │   └── class-fluentboards-integration.php
│   ├── workflow/               # Workflow management
│   │   ├── class-workflow-manager.php
│   │   ├── class-workflow-installer.php
│   │   ├── class-workflow-config.php
│   │   └── class-workflow-sla.php
│   ├── UI/                    # UI components
│   ├── accessibility/         # Accessibility features
│   ├── ai/                    # AI integrations
│   ├── analytics/            # Analytics tracking
│   ├── audit/                # Audit logging
│   ├── config/               # Configuration management
│   ├── documents/            # Document generation
│   ├── helpers/              # Utility functions
│   ├── modules/              # Feature modules
│   └── stakeholders/         # Stakeholder management
├── admin/                    # Admin interface
│   ├── css/
│   ├── js/
│   ├── partials/
│   └── templates/
├── public/                   # Public-facing features
│   ├── css/
│   ├── js/
│   └── partials/
└── assets/                   # Static resources
```

### 2. Autoloader Implementation
```php
// Project-specific namespace prefix
$prefix = 'PiperPrivacy\\';

// Autoloader logic
spl_autoload_register(function ($class) {
    // Convert namespace to file path
    $file = PIPER_PRIVACY_DIR . 'includes/' . str_replace('\\', '/', $relative_class);
    
    // Try with class- prefix
    $file = $dir_path . '/class-' . strtolower($class_name) . '.php';
    if (file_exists($file)) {
        require $file;
        return;
    }

    // Fallback without prefix
    $file = $dir_path . '/' . strtolower($class_name) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});
```

### 3. Error Handling & Logging
```php
// Custom error handler
set_error_handler('piper_privacy_error_handler', E_ALL);
set_exception_handler('piper_privacy_exception_handler');

// Debug logging
if (PIPER_PRIVACY_DEBUG) {
    error_log($message, 3, PIPER_PRIVACY_DIR . 'debug.log');
}

// Database logging
$wpdb->prefix . 'piper_privacy_audit_log'
$wpdb->prefix . 'piper_privacy_workflow_history'
```

### 4. Custom Post Types & Taxonomies
```php
- privacy_collection
  ├── Taxonomies
  │   ├── privacy_collection_status
  │   └── privacy_collection_system
  └── Capabilities
      ├── edit_privacy_collection
      ├── edit_privacy_collections
      ├── edit_others_privacy_collections
      └── ...

- privacy_threshold
  ├── Taxonomies
  │   └── privacy_threshold_status
  └── Capabilities
      ├── edit_privacy_threshold
      ├── edit_privacy_thresholds
      └── ...

- privacy_impact
  ├── Taxonomies
  │   └── privacy_impact_risk
  └── Capabilities
      ├── edit_privacy_impact
      ├── edit_privacy_impacts
      └── ...
```

### 5. Role-Based Access Control
```php
- Administrator
  └── All capabilities

- Privacy Officer
  ├── read_privacy_collection
  ├── edit_privacy_collections
  ├── publish_privacy_collections
  ├── delete_privacy_collections
  └── ...

- System Owner
  ├── read_privacy_collection
  ├── edit_privacy_collection
  └── edit_privacy_collections
```

### 6. Workflow Stages
```php
Collection Workflow:
1. Draft
   └── Initial data entry
2. PTA Required
   └── Threshold assessment needed
3. PTA In Progress
   └── Assessment being completed
4. PTA Review
   └── Assessment under review
5. PIA Required
   └── Impact assessment needed
6. PIA In Progress
   └── Impact assessment being completed
7. PIA Review
   └── Impact assessment under review
8. Implementation
   └── Controls being implemented
9. Active
   └── Collection in production
10. Under Review
    └── Periodic review
11. Retirement
    └── End-of-life planning
12. Archived
    └── Historical record
```

### 7. Database Schema
```sql
-- Audit Log
CREATE TABLE {$prefix}piper_privacy_audit_log (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    user_id bigint(20) NOT NULL,
    action varchar(50) NOT NULL,
    object_type varchar(50) NOT NULL,
    object_id bigint(20) NOT NULL,
    details longtext NOT NULL,
    created_at datetime NOT NULL,
    PRIMARY KEY (id)
);

-- Workflow History
CREATE TABLE {$prefix}piper_privacy_workflow_history (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    workflow_id bigint(20) NOT NULL,
    object_id bigint(20) NOT NULL,
    from_stage varchar(50) NOT NULL,
    to_stage varchar(50) NOT NULL,
    user_id bigint(20) NOT NULL,
    comments text,
    created_at datetime NOT NULL,
    PRIMARY KEY (id)
);
```

## Development Guidelines

### 1. Coding Standards
- WordPress Coding Standards
- PHP PSR-12
- JavaScript Standard Style
- CSS/SCSS Guidelines

### 2. Documentation
- PHPDoc blocks
- Inline comments
- README files
- API documentation
- User documentation

### 3. Testing
- Unit testing (PHPUnit)
- Integration testing
- Accessibility testing
- Performance testing
- Security testing

### 4. Version Control
- Feature branches
- Semantic versioning
- Descriptive commit messages
- Code review process
- Release management