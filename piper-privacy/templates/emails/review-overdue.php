<?php
/**
 * Review Due Email Template
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
        .meta {
            background-color: #f8d7da;
            padding: 10px;
            margin: 20px 0;
            border-left: 4px solid #dc3545;
        }
        .expiring {
            background-color: #fff3cd;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .overdue {
            background-color: #dc3545;
            color: white;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .impact {
            background-color: #f8f9fa;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
            border-left: 4px solid #dc3545;
        }
        .countdown {
            font-size: 1.2em;
            font-weight: bold;
            color: #dc3545;
            text-align: center;
            padding: 10px;
            background-color: #f8d7da;
            border-radius: 4px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>Urgent: Privacy Controls Review Required</h1>
        </div>
        <div class="content">
            <p>Dear {recipient_name},</p>

            <p>This is an urgent notification regarding expiring privacy controls and overdue reviews:</p>

            <div class="meta">
                <p><strong>Collection:</strong> {collection_title}</p>
                <p><strong>System:</strong> {system_name}</p>
                <p><strong>Last Review:</strong> {last_review_date}</p>
                <p><strong>Status:</strong> {review_status}</p>
            </div>

            <div class="countdown">
                Days Until Critical: {days_until_critical}
            </div>

            <div class="overdue">
                <h3>Overdue Items:</h3>
                <ul>
                    {overdue_items}
                </ul>
            </div>

            <div class="expiring">
                <h3>Expiring Controls:</h3>
                <ul>
                    {expiring_controls}
                </ul>
            </div>

            <div class="impact">
                <h3>Potential Impact:</h3>
                <ul>
                    <li>Non-compliance with privacy regulations</li>
                    <li>Increased risk of privacy incidents</li>
                    <li>Possible suspension of collection activities</li>
                    <li>Required reporting to oversight bodies</li>
                </ul>
            </div>

            <p>
                <a href="{review_url}" class="button">Address Compliance Issues Now</a>
            </p>

            <p>Immediate action is required to maintain compliance and ensure proper privacy protection measures remain in place.</p>

            <p>If you need assistance or clarification, please contact the Privacy Office immediately.</p>
        </div>
        <div class="footer">
            <p>This is an automated message from {site_name}. Please do not reply to this email.</p>
            <p>Reference: {collection_id} | Alert ID: {alert_id}</p>
            <p>Escalation Level: {escalation_level} | Notifications Sent: {notification_count}</p>
        </div>
    </div>
</body>
</html>