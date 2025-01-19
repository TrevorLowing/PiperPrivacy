<?php
/**
 * Action Required Reminder Email Template
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
            background-color: #ffc107;
            color: #000;
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
            background-color: #ffc107;
            color: #000;
            text-decoration: none;
            border-radius: 4px;
            margin: 20px 0;
        }
        .meta {
            background-color: #f5f5f5;
            padding: 10px;
            margin: 20px 0;
            border-left: 4px solid #ffc107;
        }
        .deadline {
            color: #dc3545;
            font-weight: bold;
        }
        .countdown {
            background-color: #f8d7da;
            border-radius: 4px;
            padding: 15px;
            margin: 10px 0;
            text-align: center;
            font-size: 1.2em;
        }
        .action-required {
            background-color: #fff3cd;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .consequence {
            background-color: #f8f9fa;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
            border-left: 4px solid #dc3545;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>Action Required: Approaching Deadline</h1>
        </div>
        <div class="content">
            <p>Dear {recipient_name},</p>

            <p>This is a reminder that action is required for the following privacy collection:</p>

            <div class="meta">
                <p><strong>Collection:</strong> {collection_title}</p>
                <p><strong>Stage:</strong> {current_stage}</p>
                <p><strong>Action Required:</strong> {required_action}</p>
                <p><strong>Due Date:</strong> <span class="deadline">{due_date}</span></p>
            </div>

            <div class="countdown">
                Time Remaining: <strong>{days_remaining} days</strong>
            </div>

            <div class="action-required">
                <h3>Required Actions:</h3>
                <ul>
                    {action_items}
                </ul>
            </div>

            <div class="consequence">
                <h3>If No Action is Taken:</h3>
                <ul>
                    <li>The workflow will be marked as blocked</li>
                    <li>Escalation notifications will be sent to supervisors</li>
                    <li>Compliance status may be affected</li>
                    <li>Additional review requirements may be triggered</li>
                </ul>
            </div>

            <p>
                <a href="{action_url}" class="button">Take Action Now</a>
            </p>

            <p>If you need assistance or require an extension, please contact the Privacy Office immediately.</p>

            <p>Note: Extensions must be requested at least 48 hours before the current deadline.</p>
        </div>
        <div class="footer">
            <p>This is an automated message from {site_name}. Please do not reply to this email.</p>
            <p>Reference: {collection_id} | Task ID: {task_id}</p>
            <p>Reminder {reminder_count} of {total_reminders}</p>
        </div>
    </div>
</body>
</html>