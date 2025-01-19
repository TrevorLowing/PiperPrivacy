<?php
/**
 * Privacy Threshold Assessment Form Template
 *
 * @package PiperPrivacy
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="piper-privacy-form-wrapper">
    <div class="piper-privacy-progress">
        <div class="piper-privacy-progress-bar">
            <div class="piper-privacy-progress-step active">
                <div class="piper-privacy-progress-marker"></div>
                <div class="piper-privacy-progress-label"><?php esc_html_e('System Info', 'piper-privacy'); ?></div>
            </div>
            <div class="piper-privacy-progress-step">
                <div class="piper-privacy-progress-marker"></div>
                <div class="piper-privacy-progress-label"><?php esc_html_e('Data Processing', 'piper-privacy'); ?></div>
            </div>
            <div class="piper-privacy-progress-step">
                <div class="piper-privacy-progress-marker"></div>
                <div class="piper-privacy-progress-label"><?php esc_html_e('Risk Assessment', 'piper-privacy'); ?></div>
            </div>
        </div>
    </div>

    <?php
    // Display any form errors
    if (!empty($GLOBALS['rwmb_frontend_form_errors'])) {
        echo '<div class="rwmb-error-wrap">';
        foreach ($GLOBALS['rwmb_frontend_form_errors'] as $error) {
            echo '<div class="rwmb-error">' . esc_html($error) . '</div>';
        }
        echo '</div>';
    }
    ?>

    <form class="rwmb-form" method="post" enctype="multipart/form-data">
        <?php wp_nonce_field('piper_privacy_form'); ?>
        
        <!-- Step 1: System Information -->
        <div class="piper-privacy-step" data-step="1">
            <h3><?php esc_html_e('System Information', 'piper-privacy'); ?></h3>
            
            <div class="rwmb-field">
                <div class="rwmb-label">
                    <label for="system_name"><?php esc_html_e('System Name', 'piper-privacy'); ?> <span class="rwmb-required">*</span></label>
                </div>
                <div class="rwmb-input">
                    <input type="text" id="system_name" name="system_name" required>
                </div>
            </div>

            <div class="rwmb-field">
                <div class="rwmb-label">
                    <label for="system_description"><?php esc_html_e('System Description', 'piper-privacy'); ?> <span class="rwmb-required">*</span></label>
                </div>
                <div class="rwmb-input">
                    <?php
                    wp_editor('', 'system_description', [
                        'textarea_name' => 'system_description',
                        'textarea_rows' => 5,
                        'teeny' => true,
                    ]);
                    ?>
                </div>
            </div>

            <div class="rwmb-field">
                <div class="rwmb-label">
                    <label for="system_owner"><?php esc_html_e('System Owner', 'piper-privacy'); ?> <span class="rwmb-required">*</span></label>
                </div>
                <div class="rwmb-input">
                    <input type="text" id="system_owner" name="system_owner" required>
                </div>
            </div>

            <div class="rwmb-field">
                <div class="rwmb-label">
                    <label for="system_status"><?php esc_html_e('System Status', 'piper-privacy'); ?> <span class="rwmb-required">*</span></label>
                </div>
                <div class="rwmb-input">
                    <select id="system_status" name="system_status" required>
                        <option value=""><?php esc_html_e('Select Status', 'piper-privacy'); ?></option>
                        <option value="operational"><?php esc_html_e('Operational', 'piper-privacy'); ?></option>
                        <option value="development"><?php esc_html_e('In Development', 'piper-privacy'); ?></option>
                        <option value="planning"><?php esc_html_e('Planning', 'piper-privacy'); ?></option>
                        <option value="retired"><?php esc_html_e('Retired', 'piper-privacy'); ?></option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Step 2: Data Processing -->
        <div class="piper-privacy-step" data-step="2" style="display: none;">
            <h3><?php esc_html_e('Data Processing', 'piper-privacy'); ?></h3>

            <div class="rwmb-field">
                <div class="rwmb-label">
                    <label><?php esc_html_e('PII Categories', 'piper-privacy'); ?> <span class="rwmb-required">*</span></label>
                </div>
                <div class="rwmb-input">
                    <?php
                    $pii_categories = [
                        'general_personal' => __('General Personal Information', 'piper-privacy'),
                        'contact' => __('Contact Information', 'piper-privacy'),
                        'government_id' => __('Government ID Numbers', 'piper-privacy'),
                        'financial' => __('Financial Information', 'piper-privacy'),
                        'health' => __('Health Information', 'piper-privacy'),
                        'biometric' => __('Biometric Data', 'piper-privacy'),
                        'genetic' => __('Genetic Data', 'piper-privacy'),
                        'location' => __('Location Data', 'piper-privacy'),
                        'criminal' => __('Criminal Records', 'piper-privacy'),
                        'children' => __('Children\'s Data', 'piper-privacy'),
                    ];

                    foreach ($pii_categories as $value => $label) {
                        echo '<label class="rwmb-checkbox-wrapper">';
                        echo '<input type="checkbox" name="pii_categories[]" value="' . esc_attr($value) . '">';
                        echo esc_html($label);
                        echo '</label>';
                    }
                    ?>
                </div>
            </div>

            <div class="rwmb-field">
                <div class="rwmb-label">
                    <label for="processing_purposes"><?php esc_html_e('Processing Purposes', 'piper-privacy'); ?> <span class="rwmb-required">*</span></label>
                </div>
                <div class="rwmb-input">
                    <?php
                    wp_editor('', 'processing_purposes', [
                        'textarea_name' => 'processing_purposes',
                        'textarea_rows' => 5,
                        'teeny' => true,
                    ]);
                    ?>
                </div>
            </div>

            <div class="rwmb-field">
                <div class="rwmb-label">
                    <label for="data_volume"><?php esc_html_e('Data Volume', 'piper-privacy'); ?> <span class="rwmb-required">*</span></label>
                </div>
                <div class="rwmb-input">
                    <select id="data_volume" name="data_volume" required>
                        <option value=""><?php esc_html_e('Select Volume', 'piper-privacy'); ?></option>
                        <option value="small"><?php esc_html_e('Small (< 1,000 records)', 'piper-privacy'); ?></option>
                        <option value="medium"><?php esc_html_e('Medium (1,000 - 10,000 records)', 'piper-privacy'); ?></option>
                        <option value="large"><?php esc_html_e('Large (10,000 - 100,000 records)', 'piper-privacy'); ?></option>
                        <option value="very_large"><?php esc_html_e('Very Large (> 100,000 records)', 'piper-privacy'); ?></option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Step 3: Risk Assessment -->
        <div class="piper-privacy-step" data-step="3" style="display: none;">
            <h3><?php esc_html_e('Risk Assessment', 'piper-privacy'); ?></h3>

            <div class="rwmb-field">
                <div class="rwmb-label">
                    <label><?php esc_html_e('Risk Factors', 'piper-privacy'); ?> <span class="rwmb-required">*</span></label>
                </div>
                <div class="rwmb-input">
                    <?php
                    $risk_factors = [
                        'sensitive_data' => __('Processing of sensitive data', 'piper-privacy'),
                        'large_scale' => __('Large scale processing', 'piper-privacy'),
                        'monitoring' => __('Systematic monitoring', 'piper-privacy'),
                        'automated_decision' => __('Automated decision making', 'piper-privacy'),
                        'vulnerable_subjects' => __('Processing data of vulnerable subjects', 'piper-privacy'),
                        'new_technology' => __('Using innovative or new technologies', 'piper-privacy'),
                        'cross_border' => __('Cross-border data transfers', 'piper-privacy'),
                        'prevent_rights' => __('Preventing data subjects from exercising rights', 'piper-privacy'),
                    ];

                    foreach ($risk_factors as $value => $label) {
                        echo '<label class="rwmb-checkbox-wrapper">';
                        echo '<input type="checkbox" name="risk_factors[]" value="' . esc_attr($value) . '">';
                        echo esc_html($label);
                        echo '</label>';
                    }
                    ?>
                </div>
            </div>

            <div class="rwmb-field">
                <div class="rwmb-label">
                    <label for="risk_level"><?php esc_html_e('Overall Risk Level', 'piper-privacy'); ?> <span class="rwmb-required">*</span></label>
                </div>
                <div class="rwmb-input">
                    <select id="risk_level" name="risk_level" required>
                        <option value=""><?php esc_html_e('Select Risk Level', 'piper-privacy'); ?></option>
                        <option value="low"><?php esc_html_e('Low', 'piper-privacy'); ?></option>
                        <option value="medium"><?php esc_html_e('Medium', 'piper-privacy'); ?></option>
                        <option value="high"><?php esc_html_e('High', 'piper-privacy'); ?></option>
                        <option value="very_high"><?php esc_html_e('Very High', 'piper-privacy'); ?></option>
                    </select>
                </div>
            </div>

            <div class="rwmb-field">
                <div class="rwmb-label">
                    <label for="pta_recommendation"><?php esc_html_e('PTA Recommendation', 'piper-privacy'); ?> <span class="rwmb-required">*</span></label>
                </div>
                <div class="rwmb-input">
                    <select id="pta_recommendation" name="pta_recommendation" required>
                        <option value=""><?php esc_html_e('Select Recommendation', 'piper-privacy'); ?></option>
                        <option value="proceed"><?php esc_html_e('Proceed - Low Risk', 'piper-privacy'); ?></option>
                        <option value="proceed_with_measures"><?php esc_html_e('Proceed with Additional Measures', 'piper-privacy'); ?></option>
                        <option value="pia_required"><?php esc_html_e('PIA Required', 'piper-privacy'); ?></option>
                        <option value="do_not_proceed"><?php esc_html_e('Do Not Proceed - High Risk', 'piper-privacy'); ?></option>
                    </select>
                </div>
            </div>

            <div class="rwmb-field">
                <div class="rwmb-label">
                    <label for="recommendation_rationale"><?php esc_html_e('Recommendation Rationale', 'piper-privacy'); ?> <span class="rwmb-required">*</span></label>
                </div>
                <div class="rwmb-input">
                    <?php
                    wp_editor('', 'recommendation_rationale', [
                        'textarea_name' => 'recommendation_rationale',
                        'textarea_rows' => 5,
                        'teeny' => true,
                    ]);
                    ?>
                </div>
            </div>
        </div>

        <div class="rwmb-submit-wrap">
            <button type="button" class="rwmb-button" id="prev-step" style="display: none;"><?php esc_html_e('Previous', 'piper-privacy'); ?></button>
            <button type="button" class="rwmb-button" id="next-step"><?php esc_html_e('Next', 'piper-privacy'); ?></button>
            <button type="submit" class="rwmb-submit-button" style="display: none;"><?php esc_html_e('Submit Threshold Assessment', 'piper-privacy'); ?></button>
        </div>
    </form>
</div>
