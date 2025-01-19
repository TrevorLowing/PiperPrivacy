# PiperPrivacy WordPress Plugin

A comprehensive privacy management system for WordPress with breach notification, risk assessment, and compliance management features.

## Features

- **Breach Notification Management**
  - Document management with retention tracking
  - Risk assessment and scoring
  - Multi-framework compliance analysis (GDPR, CCPA, HIPAA)
  - Automated notifications and deadlines

- **Export and Reporting**
  - Multiple export formats (PDF, CSV, JSON)
  - Document archives
  - Compliance reports
  - Audit trails

- **Notification System**
  - Deadline alerts
  - Document retention reminders
  - Status change notifications
  - Email integration

- **Compliance Management**
  - Automated compliance checks
  - Multi-framework support
  - Real-time monitoring
  - Documentation tracking

## Requirements

- WordPress 6.0 or higher
- PHP 8.0 or higher
- Composer for dependency management

## Installation

1. Clone this repository to your WordPress plugins directory:
   ```bash
   cd wp-content/plugins
   git clone [repository-url] piper-privacy
   ```

2. Install dependencies using Composer:
   ```bash
   cd piper-privacy
   composer install
   ```

3. Activate the plugin through the WordPress admin interface.

## Configuration

1. Go to WordPress admin panel
2. Navigate to PiperPrivacy > Settings
3. Configure your notification preferences and compliance settings

## Usage

### Managing Breaches

1. Go to PiperPrivacy > Breaches
2. Click "Add New" to create a breach notification
3. Fill in the breach details
4. Upload relevant documents
5. Run risk assessment and compliance analysis

### Document Management

1. Navigate to a breach record
2. Use the document upload section
3. Add documents with proper categorization
4. Monitor retention dates

### Exporting Data

1. Open a breach record
2. Click the export button
3. Choose your preferred format (PDF, CSV, JSON)
4. Download the report

### Monitoring Notifications

1. Check the notification center for alerts
2. Review deadline notifications
3. Monitor document retention alerts
4. Track compliance updates

## Development

### Building Assets

```bash
npm install
npm run build
```

### Running Tests

```bash
composer test
```

## Support

For support, please [create an issue](repository-issues-url) or contact our support team.

## License

This project is licensed under the GPL v2 or later - see the [LICENSE](LICENSE) file for details.
