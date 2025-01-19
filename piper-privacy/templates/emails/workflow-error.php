<?php
/**
 * Error Notification Email Template
 */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .header {
            background-color: #dc3545;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 20px;
            background-color: #ffffff;
        }
        .footer {
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 20px 0;
        }
        .error-box {
            background-color: #f8d7da;
            border: 1px solid #f5c2c7;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
            color: #842029;
        }
        .error-details {
            background-color: #f8f9fa;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
            font-family: monospace;
        }
        .urgent {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>Privacy Workflow Error</h1>
        </div>
        <div class="content">
            <p>Dear {recipient_name},</p>

            <p><span class="urgent">Urgent:</span> An error has occurred in the privacy workflow process.</p>

            <div class="error-box">
                <h3>Error Information:</h3>
                <p><strong>Type:</strong> {error_type}</p>
                <p><strong>Collection:</strong> {collection_title}</p>
                <p><strong>Stage:</strong> {workflow_stage}</p>
                <p><strong>Timestamp:</strong> {error_timestamp}</p>
            </div>

            <div class="error-details">
                <h3>Error Details:</h3>
                <pre>{error_message}</pre>
                <p><strong>Stack Trace:</strong></p>
                <pre>{error_trace}</pre>
            </div>

            <p>
                <a href="{error_details_url}" class="button">View Error Details</a>
            </p>

            <p>Immediate attention is required to resolve this issue and ensure the privacy workflow continues properly.</p>

            <p>If you need assistance, please contact the system administrator or technical support team.</p>
        </div>
        <div class="footer">
            <p>This is an automated message from {site_name}. Please do not reply to this email.</p>
            <p>Reference: {collection_id} | Error ID: {error_id}</p>
        </div>
    </div>
</body>
</html>