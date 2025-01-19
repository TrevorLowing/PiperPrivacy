<?php
/**
 * Retirement Email Template
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
            background-color: #6c757d;
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
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 20px 0;
        }
        .meta {
            background-color: #f5f5f5;
            padding: 10px;
            margin: 20px 0;
            border-left: 4px solid #6c757d;
        }
        .phase {
            background-color: #f8f9fa;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .important {
            color: #dc3545;
            font-weight: bold;
        }
        .dates {
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
            <h1>Privacy Collection Retirement</h1>
        </div>
        <div class="content">
            <p>Dear {recipient_name},</p>

            <p>The retirement process has been initiated for the following privacy collection:</p>

            <div class="meta">
                <p><strong>Collection:</strong> {collection_title}</p>
                <p><strong>System:</strong> {system_name}</p>
                <p><strong>Active Since:</strong> {active_date}</p>
                <p><strong>Retirement Date:</strong> <span class="important">{retirement_date}</span></p>
            </div>

            <div class="phase">
                <h3>Retirement Phases:</h3>
                <ol>
                    <li>Review and Documentation (Due: {review_deadline})</li>
                    <li>Data Disposition Planning (Due: {planning_deadline})</li>
                    <li>Final Data Cleanup (Due: {cleanup_deadline})</li>
                    <li>Archive Creation (Due: {archive_deadline})</li>
                </ol>
            </div>

            <div class="dates">
                <h3>Key Dates:</h3>
                <ul>
                    <li><strong>Stakeholder Review:</strong> {stakeholder_review_date}</li>
                    <li><strong>Data Cleanup Start:</strong> {cleanup_start_date}</li>
                    <li><strong>Final Archive:</strong> {archive_date}</li>
                    <li><strong>System Decommission:</strong> {decommission_date}</li>
                </ul>
            </div>

            <p><span class="important">Important:</span> Please ensure all necessary data is properly identified for:</p>
            <ul>
                <li>Retention requirements</li>
                <li>Legal holds</li>
                <li>Archive preservation</li>
                <li>Secure disposal</li>
            </ul>

            <p>
                <a href="{retirement_plan_url}" class="button">View Retirement Plan</a>
            </p>

            <p>Your prompt attention to this process will ensure proper handling of privacy-related information and compliance with data protection requirements.</p>

            <p>The Privacy Office will coordinate the retirement process and provide guidance throughout each phase.</p>
        </div>
        <div class="footer">
            <p>This is an automated message from {site_name}. Please do not reply to this email.</p>
            <p>Reference: {collection_id} | Retirement Plan ID: {retirement_id}</p>
        </div>
    </div>
</body>
</html>