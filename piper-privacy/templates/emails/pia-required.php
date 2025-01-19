<?php
/**
 * PIA Required Email Template
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
            background-color: #f5f5f5;
            padding: 10px;
            margin: 20px 0;
            border-left: 4px solid #dc3545;
        }
        .risk-high {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>Privacy Impact Assessment Required</h1>
        </div>
        <div class="content">
            <p>Dear {recipient_name},</p>

            <p>Based on the Privacy Threshold Analysis results, a Privacy Impact Assessment (PIA) is required for:</p>

            <div class="meta">
                <p><strong>Collection:</strong> {collection_title}</p>
                <p><strong>System:</strong> {system_name}</p>
                <p><strong>Risk Level:</strong> <span class="risk-high">High</span></p>
                <p><strong>Due Date:</strong> {due_date}</p>
            </div>

            <p>The PIA is required due to the following factors:</p>
            <ul>
                {risk_factors}
            </ul>

            <p>Please complete the PIA by assessing:</p>
            <ul>
                <li>Detailed system analysis and data flows</li>
                <li>Privacy risks and impacts</li>
                <li>Mitigation measures</li>
                <li>Access controls and security measures</li>
                <li>Data retention and disposal plans</li>
            </ul>

            <p>
                <a href="{action_url}" class="button">Start PIA Now</a>
            </p>

            <p>The Privacy Office is available to assist with the PIA process. Please reach out if you need guidance.</p>
        </div>
        <div class="footer">
            <p>This is an automated message from {site_name}. Please do not reply to this email.</p>
            <p>Reference: {collection_id} | PTA: {pta_reference}</p>
        </div>
    </div>
</body>
</html>