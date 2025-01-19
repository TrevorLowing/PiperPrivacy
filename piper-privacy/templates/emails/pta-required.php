<?php
/**
 * PTA Required Email Template
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
            background-color: #0073aa;
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
            background-color: #0073aa;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 20px 0;
        }
        .meta {
            background-color: #f5f5f5;
            padding: 10px;
            margin: 20px 0;
            border-left: 4px solid #0073aa;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>Privacy Threshold Analysis Required</h1>
        </div>
        <div class="content">
            <p>Dear {recipient_name},</p>

            <p>A Privacy Threshold Analysis (PTA) is required for the following privacy collection:</p>

            <div class="meta">
                <p><strong>Collection:</strong> {collection_title}</p>
                <p><strong>System:</strong> {system_name}</p>
                <p><strong>Due Date:</strong> {due_date}</p>
            </div>

            <p>Please complete the PTA by assessing:</p>
            <ul>
                <li>Types of personally identifiable information (PII)</li>
                <li>Privacy risks and impacts</li>
                <li>Existing privacy controls</li>
                <li>Need for a Privacy Impact Assessment (PIA)</li>
            </ul>

            <p>
                <a href="{action_url}" class="button">Start PTA Now</a>
            </p>

            <p>If you have any questions or need assistance, please contact the Privacy Office.</p>
        </div>
        <div class="footer">
            <p>This is an automated message from {site_name}. Please do not reply to this email.</p>
            <p>Reference: {collection_id}</p>
        </div>
    </div>
</body>
</html>