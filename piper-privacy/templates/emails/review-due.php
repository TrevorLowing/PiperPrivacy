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
            background-color: #0d6efd;
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
            background-color: #0d6efd;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 20px 0;
        }
        .meta {
            background-color: #f5f5f5;
            padding: 10px;
            margin: 20px 0;
            border-left: 4px solid #0d6efd;
        }
        .history {
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
        .important {
            color: #dc3545;
            font-weight: bold;
        }
        .changes {
            background-color: #fff3cd;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
            border-left: 4px solid #ffc107;
        }
        .timeline {
            background-color: #d1e7dd;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>Privacy Collection Review Required</h1>
        </div>
        <div class="content">
            <p>Dear {recipient_name},</p>

            <p>It's time for the {review_type} review of the following privacy collection:</p>

            <div class="meta">
                <p><strong>Collection:</strong> {collection_title}</p>
                <p><strong>Last Review:</strong> {last_review_date}</p>
                <p><strong>Review Due:</strong> <span class="important">{review_deadline}</span></p>
                <p><strong>Collection Status:</strong> {collection_status}</p>
            </div>

            <div class="history">
                <h3>Previous Review Summary:</h3>
                <ul>
                    <li><strong>Last Reviewer:</strong> {last_reviewer}</li>
                    <li><strong>Key Findings:</strong> {previous_findings}</li>
                    <li><strong>Actions Taken:</strong> {previous_actions}</li>
                </ul>
            </div>

            <div class="changes">
                <h3>Notable Changes Since Last Review:</h3>
                <ul>
                    {changes_list}
                </ul>
            </div>

            <div class="checklist">
                <h3>Review Requirements:</h3>
                <ul>
                    <li>Verify data collection practices remain unchanged</li>
                    <li>Assess effectiveness of privacy controls</li>
                    <li>Review access controls and permissions</li>
                    <li>Evaluate data retention compliance</li>
                    <li>Check for any new privacy risks</li>
                    <li>Update documentation as needed</li>
                </ul>
            </div>

            <div class="timeline">
                <h3>Review Timeline:</h3>
                <ol>
                    <li>Initial Assessment ({initial_assessment_date})</li>
                    <li>Stakeholder Input ({stakeholder_input_date})</li>
                    <li>Documentation Update ({documentation_date})</li>
                    <li>Final Approval ({approval_deadline})</li>
                </ol>
            </div>

            <p>
                <a href="{review_url}" class="button">Start Review Process</a>
            </p>

            <p><span class="important">Important:</span> This review is required to maintain compliance with privacy regulations and organizational policies.</p>

            <p>The Privacy Office is available to assist with the review process. Please contact us if you need guidance or have questions.</p>
        </div>
        <div class="footer">
            <p>This is an automated message from {site_name}. Please do not reply to this email.</p>
            <p>Reference: {collection_id} | Review ID: {review_id}</p>
            <p>Review Cycle: {review_cycle} | Required Frequency: {review_frequency}</p>
        </div>
    </div>
</body>
</html>