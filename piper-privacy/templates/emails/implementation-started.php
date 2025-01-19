<?php
/**
 * Implementation Start Email Template
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
            background-color: #198754;
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
            background-color: #198754;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 20px 0;
        }
        .meta {
            background-color: #f5f5f5;
            padding: 10px;
            margin: 20px 0;
            border-left: 4px solid #198754;
        }
        .timeline {
            background-color: #f8f9fa;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .checklist {
            background-color: #e9ecef;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>Privacy Controls Implementation Started</h1>
        </div>
        <div class="content">
            <p>Dear {recipient_name},</p>

            <p>The implementation phase has begun for the following privacy collection:</p>

            <div class="meta">
                <p><strong>Collection:</strong> {collection_title}</p>
                <p><strong>System:</strong> {system_name}</p>
                <p><strong>Implementation Due:</strong> {implementation_deadline}</p>
                <p><strong>Review Date:</strong> {first_review_date}</p>
            </div>

            <div class="timeline">
                <h3>Implementation Timeline:</h3>
                <ul>
                    <li>Controls Implementation: {control_deadline}</li>
                    <li>Testing Period: {testing_period}</li>
                    <li>Verification: {verification_date}</li>
                    <li>First Review: {first_review_date}</li>
                </ul>
            </div>

            <div class="checklist">
                <h3>Required Actions:</h3>
                <ul>
                    <li>Review implementation plan</li>
                    <li>Verify privacy control requirements</li>
                    <li>Confirm resource assignments</li>
                    <li>Schedule implementation meetings</li>
                    <li>Plan testing procedures</li>
                </ul>
            </div>

            <p>
                <a href="{implementation_url}" class="button">View Implementation Plan</a>
            </p>

            <p>Regular status updates will be provided throughout the implementation phase. Please ensure all team members are aware of their responsibilities.</p>

            <p>The Privacy Office is available to assist with any questions or concerns during the implementation process.</p>
        </div>
        <div class="footer">
            <p>This is an automated message from {site_name}. Please do not reply to this email.</p>
            <p>Reference: {collection_id} | Implementation ID: {implementation_id}</p>
        </div>
    </div>
</body>
</html>