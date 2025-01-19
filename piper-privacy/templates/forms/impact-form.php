<?php
/**
 * Privacy Impact Assessment Form Template
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
                <div class="piper-privacy-progress-label"><?php esc_html_e('Overview', 'piper-privacy'); ?></div>
            </div>
            <div class="piper-privacy-progress-step">
                <div class="piper-privacy-progress-marker"></div>
                <div class="piper-privacy-progress-label"><?php esc_html_e('Data Flow', 'piper-privacy'); ?></div>
            </div>
            <div class="piper-privacy-progress-step">
                <div class="piper-privacy-progress-marker"></div>
                <div class="piper-privacy-progress-label"><?php esc_html_e('Privacy Principles', 'piper-privacy'); ?></div>
            </div>
            <div class="piper-privacy-progress-step">
                <div class="piper-privacy-progress-marker"></div>
                <div class="piper-privacy-progress-label"><?php esc_html_e('Risks & Mitigation', 'piper-privacy'); ?></div>
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
        
        <!-- Step 1: System Overview -->
        <div class="piper-privacy-step" data-step="1">
            <h3><?php esc_html_e('System Overview', 'piper-privacy'); ?></h3>
            
            <div class="rwmb-field">
                <div class="rwmb-label">
                    <label for="system_overview"><?php esc_html_e('System Overview', 'piper-privacy'); ?> <span class="rwmb-required">*</span></label>
                </div>
                <div class="rwmb-input">
                    <?php
                    wp_editor('', 'system_overview', [
                        'textarea_name' => 'system_overview',
                        'textarea_rows' => 5,
                        'teeny' => true,
                    ]);
                    ?>
                </div>
            </div>

            <div class="rwmb-field">
                <div class="rwmb-label">
                    <label for="project_scope"><?php esc_html_e('Project Scope', 'piper-privacy'); ?> <span class="rwmb-required">*</span></label>
                </div>
                <div class="rwmb-input">
                    <?php
                    wp_editor('', 'project_scope', [
                        'textarea_name' => 'project_scope',
                        'textarea_rows' => 5,
                        'teeny' => true,
                    ]);
                    ?>
                </div>
            </div>

            <div class="rwmb-field">
                <div class="rwmb-label">
                    <label for="stakeholders"><?php esc_html_e('Key Stakeholders', 'piper-privacy'); ?> <span class="rwmb-required">*</span></label>
                </div>
                <div class="rwmb-input">
                    <textarea id="stakeholders" name="stakeholders" rows="4" required></textarea>
                </div>
            </div>
        </div>

        <!-- Step 2: Data Flow Analysis -->
        <div class="piper-privacy-step" data-step="2" style="display: none;">
            <h3><?php esc_html_e('Data Flow Analysis', 'piper-privacy'); ?></h3>

            <div class="rwmb-field">
                <div class="rwmb-label">
                    <label for="data_flow"><?php esc_html_e('Data Flow Description', 'piper-privacy'); ?> <span class="rwmb-required">*</span></label>
                </div>
                <div class="rwmb-input">
                    <?php
                    wp_editor('', 'data_flow', [
                        'textarea_name' => 'data_flow',
                        'textarea_rows' => 5,
                        'teeny' => true,
                    ]);
                    ?>
                </div>
            </div>

            <div class="rwmb-field">
                <div class="rwmb-label">
                    <label for="data_flow_diagram"><?php esc_html_e('Data Flow Diagram', 'piper-privacy'); ?></label>
                </div>
                <div class="rwmb-input">
                    <input type="file" id="data_flow_diagram" name="data_flow_diagram" accept="image/*,.pdf">
                    <p class="description"><?php esc_html_e('Upload a data flow diagram (PDF or image)', 'piper-privacy'); ?></p>
                </div>
            </div>

            <div class="rwmb-field">
                <div class="rwmb-label">
                    <label for="cross_border_transfers"><?php esc_html_e('Cross-Border Transfers', 'piper-privacy'); ?></label>
                </div>
                <div class="rwmb-input">
                    <textarea id="cross_border_transfers" name="cross_border_transfers" rows="4"></textarea>
                    <p class="description"><?php esc_html_e('Describe any international data transfers', 'piper-privacy'); ?></p>
                </div>
            </div>
        </div>

        <!-- Step 3: Privacy Principles Assessment -->
        <div class="piper-privacy-step" data-step="3" style="display: none;">
            <h3><?php esc_html_e('Privacy Principles Assessment', 'piper-privacy'); ?></h3>

            <div class="rwmb-field rwmb-group-wrapper">
                <div class="rwmb-label">
                    <label><?php esc_html_e('Privacy Principles', 'piper-privacy'); ?></label>
                </div>
                <div class="rwmb-input">
                    <?php
                    $privacy_principles = [
                        'lawfulness' => __('Lawfulness, Fairness and Transparency', 'piper-privacy'),
                        'purpose_limitation' => __('Purpose Limitation', 'piper-privacy'),
                        'data_minimization' => __('Data Minimization', 'piper-privacy'),
                        'accuracy' => __('Accuracy', 'piper-privacy'),
                        'storage_limitation' => __('Storage Limitation', 'piper-privacy'),
                        'integrity_confidentiality' => __('Integrity and Confidentiality', 'piper-privacy'),
                        'accountability' => __('Accountability', 'piper-privacy'),
                    ];

                    foreach ($privacy_principles as $key => $principle) :
                    ?>
                        <div class="rwmb-group-clone">
                            <div class="rwmb-field">
                                <div class="rwmb-label">
                                    <label><?php echo esc_html($principle); ?></label>
                                </div>
                                <div class="rwmb-input">
                                    <textarea name="privacy_principles[<?php echo esc_attr($key); ?>][assessment]" rows="4" placeholder="<?php esc_attr_e('Describe how this principle is addressed', 'piper-privacy'); ?>"></textarea>
                                    <select name="privacy_principles[<?php echo esc_attr($key); ?>][compliance_status]">
                                        <option value="compliant"><?php esc_html_e('Compliant', 'piper-privacy'); ?></option>
                                        <option value="partially_compliant"><?php esc_html_e('Partially Compliant', 'piper-privacy'); ?></option>
                                        <option value="non_compliant"><?php esc_html_e('Non-Compliant', 'piper-privacy'); ?></option>
                                        <option value="not_applicable"><?php esc_html_e('Not Applicable', 'piper-privacy'); ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Step 4: Risks and Mitigation -->
        <div class="piper-privacy-step" data-step="4" style="display: none;">
            <h3><?php esc_html_e('Risks and Mitigation Measures', 'piper-privacy'); ?></h3>

            <div class="rwmb-field rwmb-group-wrapper">
                <div class="rwmb-label">
                    <label><?php esc_html_e('Privacy Risks', 'piper-privacy'); ?></label>
                </div>
                <div class="rwmb-input">
                    <div class="rwmb-group-clone">
                        <div class="rwmb-field">
                            <div class="rwmb-label">
                                <label><?php esc_html_e('Risk Name', 'piper-privacy'); ?></label>
                            </div>
                            <div class="rwmb-input">
                                <input type="text" name="privacy_risks[0][risk_name]">
                            </div>
                        </div>

                        <div class="rwmb-field">
                            <div class="rwmb-label">
                                <label><?php esc_html_e('Description', 'piper-privacy'); ?></label>
                            </div>
                            <div class="rwmb-input">
                                <textarea name="privacy_risks[0][description]" rows="3"></textarea>
                            </div>
                        </div>

                        <div class="rwmb-field">
                            <div class="rwmb-label">
                                <label><?php esc_html_e('Impact Level', 'piper-privacy'); ?></label>
                            </div>
                            <div class="rwmb-input">
                                <select name="privacy_risks[0][impact_level]">
                                    <option value="low"><?php esc_html_e('Low', 'piper-privacy'); ?></option>
                                    <option value="medium"><?php esc_html_e('Medium', 'piper-privacy'); ?></option>
                                    <option value="high"><?php esc_html_e('High', 'piper-privacy'); ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="rwmb-field">
                            <div class="rwmb-label">
                                <label><?php esc_html_e('Likelihood', 'piper-privacy'); ?></label>
                            </div>
                            <div class="rwmb-input">
                                <select name="privacy_risks[0][likelihood]">
                                    <option value="low"><?php esc_html_e('Low', 'piper-privacy'); ?></option>
                                    <option value="medium"><?php esc_html_e('Medium', 'piper-privacy'); ?></option>
                                    <option value="high"><?php esc_html_e('High', 'piper-privacy'); ?></option>
                                </select>
                            </div>
                        </div>

                        <button type="button" class="rwmb-group-remove">×</button>
                    </div>
                    <button type="button" class="rwmb-group-add"><?php esc_html_e('Add Another Risk', 'piper-privacy'); ?></button>
                </div>
            </div>

            <div class="rwmb-field rwmb-group-wrapper">
                <div class="rwmb-label">
                    <label><?php esc_html_e('Mitigation Measures', 'piper-privacy'); ?></label>
                </div>
                <div class="rwmb-input">
                    <div class="rwmb-group-clone">
                        <div class="rwmb-field">
                            <div class="rwmb-label">
                                <label><?php esc_html_e('Measure Name', 'piper-privacy'); ?></label>
                            </div>
                            <div class="rwmb-input">
                                <input type="text" name="mitigation_measures[0][measure_name]">
                            </div>
                        </div>

                        <div class="rwmb-field">
                            <div class="rwmb-label">
                                <label><?php esc_html_e('Description', 'piper-privacy'); ?></label>
                            </div>
                            <div class="rwmb-input">
                                <textarea name="mitigation_measures[0][description]" rows="3"></textarea>
                            </div>
                        </div>

                        <div class="rwmb-field">
                            <div class="rwmb-label">
                                <label><?php esc_html_e('Implementation Status', 'piper-privacy'); ?></label>
                            </div>
                            <div class="rwmb-input">
                                <select name="mitigation_measures[0][implementation_status]">
                                    <option value="planned"><?php esc_html_e('Planned', 'piper-privacy'); ?></option>
                                    <option value="in_progress"><?php esc_html_e('In Progress', 'piper-privacy'); ?></option>
                                    <option value="implemented"><?php esc_html_e('Implemented', 'piper-privacy'); ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="rwmb-field">
                            <div class="rwmb-label">
                                <label><?php esc_html_e('Implementation Date', 'piper-privacy'); ?></label>
                            </div>
                            <div class="rwmb-input">
                                <input type="date" name="mitigation_measures[0][implementation_date]">
                            </div>
                        </div>

                        <button type="button" class="rwmb-group-remove">×</button>
                    </div>
                    <button type="button" class="rwmb-group-add"><?php esc_html_e('Add Another Measure', 'piper-privacy'); ?></button>
                </div>
            </div>

            <div class="rwmb-field">
                <div class="rwmb-label">
                    <label for="recommendations"><?php esc_html_e('Recommendations', 'piper-privacy'); ?> <span class="rwmb-required">*</span></label>
                </div>
                <div class="rwmb-input">
                    <?php
                    wp_editor('', 'recommendations', [
                        'textarea_name' => 'recommendations',
                        'textarea_rows' => 5,
                        'teeny' => true,
                    ]);
                    ?>
                </div>
            </div>

            <div class="rwmb-field">
                <div class="rwmb-label">
                    <label for="dpo_comments"><?php esc_html_e('DPO Comments', 'piper-privacy'); ?></label>
                </div>
                <div class="rwmb-input">
                    <?php
                    wp_editor('', 'dpo_comments', [
                        'textarea_name' => 'dpo_comments',
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
            <button type="submit" class="rwmb-submit-button" style="display: none;"><?php esc_html_e('Submit Impact Assessment', 'piper-privacy'); ?></button>
        </div>
    </form>
</div>
