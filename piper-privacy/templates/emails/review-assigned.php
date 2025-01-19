<?php
/**
 * Review Assignment Email Template
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
        .deadline {
            color: #dc3545;
            font-weight: bold;
        }
        .review-section {
            background-color: #f8f9fa;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>Privacy Review Assignment</h1>
        </div>
        <div class="content">
            <p>Dear {reviewer_name},</p>

            <p>You have been assigned to review the following privacy assessment:</p>

            <div class="meta">
                <p><strong>Collection:</strong> {collection_title}</p>
                <p><strong>Document Type:</strong> {document_type}</p>
                <p><strong>Review Deadline:</strong> <span class="deadline">{review_deadline}</span></p>
            </div>

            <div class="review-section">
                <h3>Review Responsibilities:</h3>
                <ul>
                    <li>Review assigned sections for completeness and accuracy</li>
                    <li>Verify privacy controls and mitigation measures</li>
                    <li>Provide feedback on identified risks and impacts</li>
                    <li>Submit your review decision (approve/reject/request changes)</li>
                </ul>
            </div>

            <div class="review-section">
                <h3>Assigned Sections:</h3>
                <ul>
                    {assigned_sections}
                </ul>
            </div>

            <p>
                <a href="{review_url}" class="button">Start Review</a>
            </p>

            <p>Please complete your review by the specified deadline. If you need an extension or have questions, contact the Privacy Office.</p>
        </div>
        <div class="footer">
            <p>This is an automated message from {site_name}. Please do not reply to this email.</p>
            <p>Reference: {collection_id} | Review ID: {review_id}</p>
        </div>
    </div>
</body>
</html>