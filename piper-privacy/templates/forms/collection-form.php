<?php
/**
 * Privacy Collection Registration Form Template
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
                <div class="piper-privacy-progress-label"><?php esc_html_e('Basic Info', 'piper-privacy'); ?></div>
            </div>
            <div class="piper-privacy-progress-step">
                <div class="piper-privacy-progress-marker"></div>
                <div class="piper-privacy-progress-label"><?php esc_html_e('Data Elements', 'piper-privacy'); ?></div>
            </div>
            <div class="piper-privacy-progress-step">
                <div class="piper-privacy-progress-marker"></div>
                <div class="piper-privacy-progress-label"><?php esc_html_e('Sharing', 'piper-privacy'); ?></div>
            </div>
            <div class="piper-privacy-progress-step">
                <div class="piper-privacy-progress-marker"></div>
                <div class="piper-privacy-progress-label"><?php esc_html_e('Security', 'piper-privacy'); ?></div>
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
        
        <!-- Step 1: Basic Information -->
        <div class="piper-privacy-step" data-step="1">
            <h3><?php esc_html_e('Basic Information', 'piper-privacy'); ?></h3>
            
            <div class="rwmb-field">
                <div class="rwmb-label">
                    <label for="collection_title"><?php esc_html_e('Collection Title', 'piper-privacy'); ?> <span class="rwmb-required">*</span></label>
                </div>
                <div class="rwmb-input">
                    <input type="text" id="collection_title" name="post_title" required>
                </div>
            </div>

            <div class="rwmb-field">
                <div class="rwmb-label">
                    <label for="collection_purpose"><?php esc_html_e('Purpose of Collection', 'piper-privacy'); ?> <span class="rwmb-required">*</span></label>
                </div>
                <div class="rwmb-input">
                    <?php
                    wp_editor('', 'collection_purpose', [
                        'textarea_name' => 'collection_purpose',
                        'textarea_rows' => 5,
                        'teeny' => true,
                    ]);
                    ?>
                </div>
            </div>

            <div class="rwmb-field">
                <div class="rwmb-label">
                    <label for="legal_authority"><?php esc_html_e('Legal Authority', 'piper-privacy'); ?> <span class="rwmb-required">*</span></label>
                </div>
                <div class="rwmb-input">
                    <select id="legal_authority" name="legal_authority" required>
                        <option value=""><?php esc_html_e('Select Authority', 'piper-privacy'); ?></option>
                        <option value="consent"><?php esc_html_e('Consent', 'piper-privacy'); ?></option>
                        <option value="contract"><?php esc_html_e('Contract', 'piper-privacy'); ?></option>
                        <option value="legal_obligation"><?php esc_html_e('Legal Obligation', 'piper-privacy'); ?></option>
                        <option value="vital_interests"><?php esc_html_e('Vital Interests', 'piper-privacy'); ?></option>
                        <option value="public_interest"><?php esc_html_e('Public Interest', 'piper-privacy'); ?></option>
                        <option value="legitimate_interests"><?php esc_html_e('Legitimate Interests', 'piper-privacy'); ?></option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Step 2: Data Elements -->
        <div class="piper-privacy-step" data-step="2" style="display: none;">
            <h3><?php esc_html_e('Data Elements', 'piper-privacy'); ?></h3>

            <div class="rwmb-field">
                <div class="rwmb-label">
                    <label><?php esc_html_e('Data Elements Collected', 'piper-privacy'); ?> <span class="rwmb-required">*</span></label>
                </div>
                <div class="rwmb-input">
                    <?php
                    $data_elements = [
                        'name' => __('Name', 'piper-privacy'),
                        'email' => __('Email', 'piper-privacy'),
                        'phone' => __('Phone', 'piper-privacy'),
                        'address' => __('Address', 'piper-privacy'),
                        'dob' => __('Date of Birth', 'piper-privacy'),
                        'ssn' => __('Social Security Number', 'piper-privacy'),
                        'financial' => __('Financial Information', 'piper-privacy'),
                        'health' => __('Health Information', 'piper-privacy'),
                        'biometric' => __('Biometric Data', 'piper-privacy'),
                        'location' => __('Location Data', 'piper-privacy'),
                    ];

                    foreach ($data_elements as $value => $label) {
                        echo '<label class="rwmb-checkbox-wrapper">';
                        echo '<input type="checkbox" name="data_elements[]" value="' . esc_attr($value) . '">';
                        echo esc_html($label);
                        echo '</label>';
                    }
                    ?>
                </div>
            </div>

            <div class="rwmb-field">
                <div class="rwmb-label">
                    <label for="data_sources"><?php esc_html_e('Data Sources', 'piper-privacy'); ?> <span class="rwmb-required">*</span></label>
                </div>
                <div class="rwmb-input">
                    <select id="data_sources" name="data_sources[]" multiple required>
                        <option value="direct"><?php esc_html_e('Direct from Individual', 'piper-privacy'); ?></option>
                        <option value="third_party"><?php esc_html_e('Third Party', 'piper-privacy'); ?></option>
                        <option value="public"><?php esc_html_e('Public Sources', 'piper-privacy'); ?></option>
                        <option value="derived"><?php esc_html_e('Derived/Inferred', 'piper-privacy'); ?></option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Step 3: Data Sharing -->
        <div class="piper-privacy-step" data-step="3" style="display: none;">
            <h3><?php esc_html_e('Data Sharing', 'piper-privacy'); ?></h3>

            <div class="rwmb-field rwmb-group-wrapper">
                <div class="rwmb-label">
                    <label><?php esc_html_e('Sharing Parties', 'piper-privacy'); ?></label>
                </div>
                <div class="rwmb-input">
                    <div class="rwmb-group-clone">
                        <div class="rwmb-field">
                            <div class="rwmb-label">
                                <label><?php esc_html_e('Party Name', 'piper-privacy'); ?></label>
                            </div>
                            <div class="rwmb-input">
                                <input type="text" name="sharing_parties[0][party_name]">
                            </div>
                        </div>

                        <div class="rwmb-field">
                            <div class="rwmb-label">
                                <label><?php esc_html_e('Purpose of Sharing', 'piper-privacy'); ?></label>
                            </div>
                            <div class="rwmb-input">
                                <textarea name="sharing_parties[0][purpose]" rows="3"></textarea>
                            </div>
                        </div>

                        <div class="rwmb-field">
                            <div class="rwmb-label">
                                <label><?php esc_html_e('Data Shared', 'piper-privacy'); ?></label>
                            </div>
                            <div class="rwmb-input">
                                <textarea name="sharing_parties[0][data_shared]" rows="3"></textarea>
                            </div>
                        </div>

                        <button type="button" class="rwmb-group-remove">×</button>
                    </div>
                    <button type="button" class="rwmb-group-add"><?php esc_html_e('Add Another Party', 'piper-privacy'); ?></button>
                </div>
            </div>
        </div>

        <!-- Step 4: Security Controls -->
        <div class="piper-privacy-step" data-step="4" style="display: none;">
            <h3><?php esc_html_e('Security Controls', 'piper-privacy'); ?></h3>

            <div class="rwmb-field rwmb-group-wrapper">
                <div class="rwmb-label">
                    <label><?php esc_html_e('Security Controls', 'piper-privacy'); ?></label>
                </div>
                <div class="rwmb-input">
                    <div class="rwmb-group-clone">
                        <div class="rwmb-field">
                            <div class="rwmb-label">
                                <label><?php esc_html_e('Control Name', 'piper-privacy'); ?></label>
                            </div>
                            <div class="rwmb-input">
                                <input type="text" name="security_controls[0][name]">
                            </div>
                        </div>

                        <div class="rwmb-field">
                            <div class="rwmb-label">
                                <label><?php esc_html_e('Description', 'piper-privacy'); ?></label>
                            </div>
                            <div class="rwmb-input">
                                <textarea name="security_controls[0][description]" rows="3"></textarea>
                            </div>
                        </div>

                        <div class="rwmb-field">
                            <div class="rwmb-label">
                                <label><?php esc_html_e('Status', 'piper-privacy'); ?></label>
                            </div>
                            <div class="rwmb-input">
                                <select name="security_controls[0][status]">
                                    <option value="implemented"><?php esc_html_e('Implemented', 'piper-privacy'); ?></option>
                                    <option value="planned"><?php esc_html_e('Planned', 'piper-privacy'); ?></option>
                                    <option value="in_progress"><?php esc_html_e('In Progress', 'piper-privacy'); ?></option>
                                    <option value="not_applicable"><?php esc_html_e('Not Applicable', 'piper-privacy'); ?></option>
                                </select>
                            </div>
                        </div>

                        <button type="button" class="rwmb-group-remove">×</button>
                    </div>
                    <button type="button" class="rwmb-group-add"><?php esc_html_e('Add Another Control', 'piper-privacy'); ?></button>
                </div>
            </div>

            <div class="rwmb-field">
                <div class="rwmb-label">
                    <label for="retention_period"><?php esc_html_e('Retention Period', 'piper-privacy'); ?> <span class="rwmb-required">*</span></label>
                </div>
                <div class="rwmb-input">
                    <select id="retention_period" name="retention_period" required>
                        <option value=""><?php esc_html_e('Select Period', 'piper-privacy'); ?></option>
                        <option value="1_year"><?php esc_html_e('1 Year', 'piper-privacy'); ?></option>
                        <option value="3_years"><?php esc_html_e('3 Years', 'piper-privacy'); ?></option>
                        <option value="5_years"><?php esc_html_e('5 Years', 'piper-privacy'); ?></option>
                        <option value="7_years"><?php esc_html_e('7 Years', 'piper-privacy'); ?></option>
                        <option value="permanent"><?php esc_html_e('Permanent', 'piper-privacy'); ?></option>
                    </select>
                </div>
            </div>
        </div>

        <div class="rwmb-submit-wrap">
            <button type="button" class="rwmb-button" id="prev-step" style="display: none;"><?php esc_html_e('Previous', 'piper-privacy'); ?></button>
            <button type="button" class="rwmb-button" id="next-step"><?php esc_html_e('Next', 'piper-privacy'); ?></button>
            <button type="submit" class="rwmb-submit-button" style="display: none;"><?php esc_html_e('Submit Collection Registration', 'piper-privacy'); ?></button>
        </div>
    </form>
</div>
