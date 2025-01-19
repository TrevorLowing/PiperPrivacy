<?php
/**
 * Template for the Analytics Dashboard
 *
 * @package PiperPrivacy
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <div class="analytics-dashboard">
        <div class="analytics-header">
            <h1 class="analytics-title"><?php esc_html_e('Privacy Analytics Dashboard', 'piper-privacy'); ?></h1>
            <div class="analytics-actions">
                <select id="dateRange" class="analytics-filter">
                    <option value="7"><?php esc_html_e('Last 7 Days', 'piper-privacy'); ?></option>
                    <option value="30" selected><?php esc_html_e('Last 30 Days', 'piper-privacy'); ?></option>
                    <option value="90"><?php esc_html_e('Last 90 Days', 'piper-privacy'); ?></option>
                    <option value="365"><?php esc_html_e('Last Year', 'piper-privacy'); ?></option>
                </select>
                <button id="refreshAnalytics" class="button">
                    <span class="dashicons dashicons-update"></span>
                    <?php esc_html_e('Refresh', 'piper-privacy'); ?>
                </button>
                <button id="exportReport" class="button button-primary">
                    <span class="dashicons dashicons-download"></span>
                    <?php esc_html_e('Export Report', 'piper-privacy'); ?>
                </button>
            </div>
        </div>

        <!-- Summary Metrics -->
        <div class="workflow-metrics">
            <div class="workflow-metric">
                <div id="totalCollections" class="workflow-metric-value">0</div>
                <div class="workflow-metric-label"><?php esc_html_e('Total Collections', 'piper-privacy'); ?></div>
            </div>
            <div class="workflow-metric">
                <div id="totalImpacts" class="workflow-metric-value">0</div>
                <div class="workflow-metric-label"><?php esc_html_e('Impact Assessments', 'piper-privacy'); ?></div>
            </div>
            <div class="workflow-metric">
                <div id="totalThresholds" class="workflow-metric-value">0</div>
                <div class="workflow-metric-label"><?php esc_html_e('Threshold Assessments', 'piper-privacy'); ?></div>
            </div>
            <div class="workflow-metric">
                <div id="completionRate" class="workflow-metric-value">0%</div>
                <div class="workflow-metric-label"><?php esc_html_e('Completion Rate', 'piper-privacy'); ?></div>
            </div>
        </div>

        <!-- Charts Grid -->
        <div class="analytics-grid">
            <!-- Collections Chart -->
            <div class="analytics-card">
                <div class="analytics-card-header">
                    <h2 class="analytics-card-title"><?php esc_html_e('Privacy Collections', 'piper-privacy'); ?></h2>
                </div>
                <div class="chart-container">
                    <canvas id="collectionsChart"></canvas>
                </div>
            </div>

            <!-- Impact Assessments Chart -->
            <div class="analytics-card">
                <div class="analytics-card-header">
                    <h2 class="analytics-card-title"><?php esc_html_e('Impact Assessments', 'piper-privacy'); ?></h2>
                </div>
                <div class="chart-container">
                    <canvas id="impactsChart"></canvas>
                </div>
            </div>

            <!-- Threshold Assessments Chart -->
            <div class="analytics-card">
                <div class="analytics-card-header">
                    <h2 class="analytics-card-title"><?php esc_html_e('Threshold Assessments', 'piper-privacy'); ?></h2>
                </div>
                <div class="chart-container">
                    <canvas id="thresholdsChart"></canvas>
                </div>
            </div>

            <!-- Workflow Metrics Chart -->
            <div class="analytics-card">
                <div class="analytics-card-header">
                    <h2 class="analytics-card-title"><?php esc_html_e('Workflow Analysis', 'piper-privacy'); ?></h2>
                </div>
                <div class="chart-container">
                    <canvas id="workflowChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Additional Metrics -->
        <div class="analytics-grid">
            <!-- Processing Time -->
            <div class="analytics-card">
                <div class="analytics-card-header">
                    <h2 class="analytics-card-title"><?php esc_html_e('Processing Time', 'piper-privacy'); ?></h2>
                </div>
                <div class="analytics-metric">
                    <div id="averageCompletionTime" class="metric-value">0</div>
                    <div class="metric-label"><?php esc_html_e('Avg. Days to Complete', 'piper-privacy'); ?></div>
                </div>
            </div>

            <!-- Overdue Tasks -->
            <div class="analytics-card">
                <div class="analytics-card-header">
                    <h2 class="analytics-card-title"><?php esc_html_e('Overdue Tasks', 'piper-privacy'); ?></h2>
                </div>
                <div class="analytics-metric">
                    <div id="overdueTasks" class="metric-value status-overdue">0</div>
                    <div class="metric-label"><?php esc_html_e('Tasks Past Due Date', 'piper-privacy'); ?></div>
                </div>
            </div>
        </div>
    </div>
</div>
