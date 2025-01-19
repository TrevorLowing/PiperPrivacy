# PiperPrivacy API Documentation

## Overview

PiperPrivacy exposes a REST API for managing privacy collections, thresholds, and impact assessments. All endpoints are accessible via the WordPress REST API with the namespace `piper-privacy/v1`.

## Authentication

The API uses WordPress authentication methods:
- Application Passwords (recommended)
- JWT Authentication (if configured)
- Cookie Authentication (for admin interface)

### Example Authentication
```bash
# Using Application Password
curl -X GET \
  https://your-site.com/wp-json/piper-privacy/v1/collections \
  -H 'Authorization: Basic base64_encoded_credentials'
```

## Endpoints

### Privacy Collections

#### List Collections
```http
GET /wp-json/piper-privacy/v1/collections
```

Parameters:
- `page` (int): Page number
- `per_page` (int): Items per page
- `status` (string): Collection status
- `department` (int): Department ID

Response:
```json
{
  "collections": [
    {
      "id": 123,
      "title": "Customer Data Collection",
      "status": "active",
      "department": "Sales",
      "created_at": "2025-01-19T00:00:00Z",
      "updated_at": "2025-01-19T00:00:00Z"
    }
  ],
  "total": 50,
  "pages": 5
}
```

#### Get Collection
```http
GET /wp-json/piper-privacy/v1/collections/{id}
```

Response:
```json
{
  "id": 123,
  "title": "Customer Data Collection",
  "description": "Collection of customer data for CRM",
  "status": "active",
  "department": "Sales",
  "data_categories": ["personal", "contact"],
  "retention_period": "12 months",
  "legal_basis": "consent",
  "created_at": "2025-01-19T00:00:00Z",
  "updated_at": "2025-01-19T00:00:00Z"
}
```

#### Create Collection
```http
POST /wp-json/piper-privacy/v1/collections
```

Request Body:
```json
{
  "title": "New Data Collection",
  "description": "Description of collection",
  "department": "Marketing",
  "data_categories": ["email", "preferences"],
  "retention_period": "24 months",
  "legal_basis": "legitimate_interest"
}
```

### Privacy Thresholds

#### List Thresholds
```http
GET /wp-json/piper-privacy/v1/thresholds
```

Parameters:
- `collection_id` (int): Related collection ID
- `status` (string): Assessment status
- `risk_level` (string): Risk level

#### Create Threshold Assessment
```http
POST /wp-json/piper-privacy/v1/thresholds
```

Request Body:
```json
{
  "collection_id": 123,
  "assessment_type": "initial",
  "risk_factors": {
    "data_volume": "high",
    "sensitivity": "medium",
    "processing_type": "automated"
  }
}
```

### Privacy Impact Assessments

#### List Impact Assessments
```http
GET /wp-json/piper-privacy/v1/impact-assessments
```

#### Create Impact Assessment
```http
POST /wp-json/piper-privacy/v1/impact-assessments
```

Request Body:
```json
{
  "threshold_id": 456,
  "collection_id": 123,
  "assessment_details": {
    "risks": [],
    "mitigations": [],
    "recommendations": []
  }
}
```

## Webhooks

### Available Events
- `collection.created`
- `collection.updated`
- `collection.deleted`
- `threshold.completed`
- `impact.required`
- `impact.completed`

### Webhook Format
```json
{
  "event": "collection.created",
  "timestamp": "2025-01-19T00:00:00Z",
  "data": {
    "id": 123,
    "type": "collection",
    "attributes": {}
  }
}
```

## Error Handling

### Error Response Format
```json
{
  "code": "error_code",
  "message": "Human readable message",
  "data": {
    "status": 400,
    "details": {}
  }
}
```

### Common Error Codes
- `invalid_request`: Malformed request
- `unauthorized`: Authentication required
- `forbidden`: Insufficient permissions
- `not_found`: Resource not found
- `validation_failed`: Invalid data

## Rate Limiting

- Default: 50 requests per minute
- Authenticated: 100 requests per minute
- Response Headers:
  - `X-RateLimit-Limit`
  - `X-RateLimit-Remaining`
  - `X-RateLimit-Reset`

## Versioning

The API uses semantic versioning (v1, v2, etc.). Breaking changes will result in a new version number.

## Testing

### Sandbox Environment
```http
https://your-site.com/wp-json/piper-privacy-sandbox/v1/
```

### Test Credentials
```bash
# Test Application Password
Username: test_api_user
Password: test_api_password
```

## Support

- Report issues on [GitHub](https://github.com/TrevorLowing/PiperPrivacy/issues)
- API status: [Status Page](https://status.your-site.com)
- Contact: api-support@your-domain.com

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
