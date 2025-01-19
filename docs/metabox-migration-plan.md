# MetaBox Migration Plan

## Overview
This document outlines the plan to migrate from ACF Pro to MetaBox Pro as our primary custom fields solution.

## Phase 1: Preparation

### 1.1 Dependencies Setup
- [ ] Install required MetaBox extensions:
  - MetaBox AIO
  - MB Frontend Submission
  - MB Custom Table
  - MB Relationships
  - MB Admin Columns

### 1.2 Field Helper Updates
- [ ] Update `includes/helpers/field-helpers.php`:
  ```php
  // Before
  function pp_get_field($field_name, $post_id = null) {
      // Current implementation
  }

  // After
  function pp_get_field($field_name, $post_id = null) {
      return rwmb_meta($field_name, '', $post_id);
  }
  ```

### 1.3 Create Test Environment
- [ ] Create development branch
- [ ] Set up test data
- [ ] Create rollback plan

## Phase 2: Field Groups Migration

### 2.1 Collection Fields
```php
add_filter('rwmb_meta_boxes', function($meta_boxes) {
    $meta_boxes[] = [
        'title' => 'Collection Details',
        'id' => 'privacy_collection_details',
        'post_types' => ['privacy_collection'],
        'fields' => [
            [
                'name' => 'Collection Purpose',
                'id' => 'collection_purpose',
                'type' => 'textarea',
            ],
            [
                'name' => 'Data Elements',
                'id' => 'data_elements',
                'type' => 'text_list',
                'clone' => true,
            ],
            // Additional fields...
        ],
    ];
    return $meta_boxes;
});
```

### 2.2 Threshold Fields
```php
[
    'title' => 'Threshold Assessment',
    'id' => 'privacy_threshold_details',
    'post_types' => ['privacy_threshold'],
    'fields' => [
        [
            'name' => 'System Name',
            'id' => 'system_name',
            'type' => 'text',
        ],
        [
            'name' => 'Risk Level',
            'id' => 'risk_level',
            'type' => 'select',
            'options' => [
                'low' => 'Low',
                'medium' => 'Medium',
                'high' => 'High',
            ],
        ],
        // Additional fields...
    ],
]
```

### 2.3 Impact Assessment Fields
```php
[
    'title' => 'Impact Assessment',
    'id' => 'privacy_impact_details',
    'post_types' => ['privacy_impact'],
    'fields' => [
        [
            'name' => 'Privacy Risks',
            'id' => 'privacy_risks',
            'type' => 'wysiwyg',
        ],
        [
            'name' => 'Mitigation Measures',
            'id' => 'mitigation_measures',
            'type' => 'wysiwyg',
        ],
        // Additional fields...
    ],
]
```

## Phase 3: Integration Updates

### 3.1 Document Generator
- [ ] Update `includes/documents/class-document-generator.php`
- [ ] Test all document types:
  - Collection Reports
  - Threshold Assessments
  - Impact Assessments
  - Retirement Documents

### 3.2 AI Assistant
- [ ] Update field access in `includes/ai/class-ai-assistant.php`
- [ ] Test AI analysis functionality
- [ ] Verify field mapping

### 3.3 Workflow System
- [ ] Update notification manager
- [ ] Test stakeholder notifications
- [ ] Verify workflow transitions

## Phase 4: Frontend Forms

### 4.1 Form Configuration
```php
add_filter('rwmb_frontend_submit_settings', function($settings) {
    $settings['privacy_collection'] = [
        'post_type' => 'privacy_collection',
        'post_status' => 'draft',
        'submit_button' => 'Submit Collection',
        'confirmation' => [
            'message' => 'Collection submitted successfully.',
        ],
    ];
    return $settings;
});
```

### 4.2 Form Templates
- [ ] Create form templates for:
  - Collection submission
  - Threshold assessment
  - Impact assessment
  - Review forms

## Phase 5: Testing & Validation

### 5.1 Unit Tests
- [ ] Update test fixtures
- [ ] Add MetaBox-specific tests
- [ ] Test field validation
- [ ] Test form submission

### 5.2 Integration Tests
- [ ] Test workflow functionality
- [ ] Verify document generation
- [ ] Test AI integration
- [ ] Validate form submissions

### 5.3 User Acceptance Testing
- [ ] Test admin interface
- [ ] Verify frontend forms
- [ ] Check notifications
- [ ] Validate reports

## Phase 6: Cleanup & Documentation

### 6.1 Code Cleanup
- [ ] Remove ACF dependencies
- [ ] Update composer.json
- [ ] Clean up unused code
- [ ] Update namespaces

### 6.2 Documentation Updates
- [ ] Update technical documentation
- [ ] Update API documentation
- [ ] Update setup instructions
- [ ] Update contributing guidelines

## Timeline

1. **Phase 1**: 1 day
   - Setup and preparation
   - Helper functions update

2. **Phase 2**: 2 days
   - Field group migration
   - Initial testing

3. **Phase 3**: 2 days
   - Integration updates
   - System testing

4. **Phase 4**: 1 day
   - Frontend form setup
   - Template creation

5. **Phase 5**: 2 days
   - Testing
   - Bug fixes

6. **Phase 6**: 1 day
   - Cleanup
   - Documentation

Total estimated time: 9 working days

## Rollback Plan

1. **Backup Points**
- Database backup before migration
- Code backup at each phase
- Field data export

2. **Rollback Steps**
```bash
# 1. Restore code
git checkout pre-migration

# 2. Restore database
wp db import pre_migration_backup.sql

# 3. Reactivate ACF Pro
wp plugin activate advanced-custom-fields-pro

# 4. Clear cache
wp cache flush
```

## Success Criteria

1. **Functionality**
   - All features working as before
   - No data loss
   - Improved performance

2. **Code Quality**
   - Clean architecture
   - No deprecated functions
   - Proper documentation

3. **User Experience**
   - Seamless transition
   - No UI regression
   - Improved form handling
