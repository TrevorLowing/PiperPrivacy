# PiperPrivacy API Documentation

## REST API Endpoints

### Collection Manager

```http
GET /wp-json/piper-privacy/v1/collections
POST /wp-json/piper-privacy/v1/collections
GET /wp-json/piper-privacy/v1/collections/{id}
PUT /wp-json/piper-privacy/v1/collections/{id}
DELETE /wp-json/piper-privacy/v1/collections/{id}
```

### Impact Assessment

```http
GET /wp-json/piper-privacy/v1/assessments
POST /wp-json/piper-privacy/v1/assessments
GET /wp-json/piper-privacy/v1/assessments/{id}
PUT /wp-json/piper-privacy/v1/assessments/{id}
DELETE /wp-json/piper-privacy/v1/assessments/{id}
```

### Consent Manager

```http
GET /wp-json/piper-privacy/v1/consents
POST /wp-json/piper-privacy/v1/consents
GET /wp-json/piper-privacy/v1/consents/{id}
PUT /wp-json/piper-privacy/v1/consents/{id}
DELETE /wp-json/piper-privacy/v1/consents/{id}
```

### Breach Notification

```http
GET /wp-json/piper-privacy/v1/breaches
POST /wp-json/piper-privacy/v1/breaches
GET /wp-json/piper-privacy/v1/breaches/{id}
PUT /wp-json/piper-privacy/v1/breaches/{id}
DELETE /wp-json/piper-privacy/v1/breaches/{id}
```

### Compliance Tracker

```http
GET /wp-json/piper-privacy/v1/compliance
POST /wp-json/piper-privacy/v1/compliance
GET /wp-json/piper-privacy/v1/compliance/{id}
PUT /wp-json/piper-privacy/v1/compliance/{id}
DELETE /wp-json/piper-privacy/v1/compliance/{id}
```

## PHP Functions

### Collection Manager

```php
// Get collection data
pp_get_collection($id);
pp_get_collections($args);
pp_create_collection($data);
pp_update_collection($id, $data);
pp_delete_collection($id);

// Validate collection
pp_validate_collection($data);
```

### Impact Assessment

```php
// Manage assessments
pp_get_assessment($id);
pp_get_assessments($args);
pp_create_assessment($data);
pp_update_assessment($id, $data);
pp_delete_assessment($id);

// Validate assessment
pp_validate_assessment($data);
```

### Consent Manager

```php
// Manage consent
pp_get_consent($id);
pp_get_consents($args);
pp_create_consent($data);
pp_update_consent($id, $data);
pp_delete_consent($id);

// Validate consent
pp_validate_consent($data);
```

### Breach Notification

```php
// Manage breaches
pp_get_breach($id);
pp_get_breaches($args);
pp_create_breach($data);
pp_update_breach($id, $data);
pp_delete_breach($id);

// Validate breach
pp_validate_breach($data);
```

### Compliance Tracker

```php
// Track compliance
pp_get_compliance($id);
pp_get_compliance_records($args);
pp_create_compliance_record($data);
pp_update_compliance_record($id, $data);
pp_delete_compliance_record($id);

// Validate compliance
pp_validate_compliance($data);
```

## Hooks

### Actions

```php
// Collection lifecycle
do_action('pp_before_collection_save', $data);
do_action('pp_after_collection_save', $id, $data);
do_action('pp_before_collection_delete', $id);
do_action('pp_after_collection_delete', $id);

// Impact assessment
do_action('pp_before_assessment_save', $data);
do_action('pp_after_assessment_save', $id, $data);
do_action('pp_before_assessment_delete', $id);
do_action('pp_after_assessment_delete', $id);

// Consent management
do_action('pp_before_consent_save', $data);
do_action('pp_after_consent_save', $id, $data);
do_action('pp_before_consent_delete', $id);
do_action('pp_after_consent_delete', $id);

// Breach notification
do_action('pp_before_breach_save', $data);
do_action('pp_after_breach_save', $id, $data);
do_action('pp_before_breach_delete', $id);
do_action('pp_after_breach_delete', $id);

// Compliance tracking
do_action('pp_before_compliance_save', $data);
do_action('pp_after_compliance_save', $id, $data);
do_action('pp_before_compliance_delete', $id);
do_action('pp_after_compliance_delete', $id);
```

### Filters

```php
// Collection management
apply_filters('pp_collection_data', $data, $id);
apply_filters('pp_collection_fields', $fields);
apply_filters('pp_collection_validation_rules', $rules);

// Impact assessment
apply_filters('pp_assessment_data', $data, $id);
apply_filters('pp_assessment_fields', $fields);
apply_filters('pp_assessment_validation_rules', $rules);

// Consent management
apply_filters('pp_consent_data', $data, $id);
apply_filters('pp_consent_fields', $fields);
apply_filters('pp_consent_validation_rules', $rules);

// Breach notification
apply_filters('pp_breach_data', $data, $id);
apply_filters('pp_breach_fields', $fields);
apply_filters('pp_breach_validation_rules', $rules);

// Compliance tracking
apply_filters('pp_compliance_data', $data, $id);
apply_filters('pp_compliance_fields', $fields);
apply_filters('pp_compliance_validation_rules', $rules);
```
