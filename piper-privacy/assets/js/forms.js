/* global jQuery, piperPrivacyForms */
(function($) {
    'use strict';

    // Form validation
    function validateForm($form) {
        const $requiredFields = $form.find('[required]');
        let isValid = true;

        $requiredFields.each(function() {
            const $field = $(this);
            const $wrapper = $field.closest('.rwmb-field');
            const $error = $wrapper.find('.rwmb-error');
            
            if (!$field.val()) {
                isValid = false;
                if (!$error.length) {
                    $wrapper.append(`<div class="rwmb-error">${piperPrivacyForms.i18n.required}</div>`);
                }
                $field.addClass('rwmb-invalid');
            } else {
                $error.remove();
                $field.removeClass('rwmb-invalid');
            }
        });

        return isValid;
    }

    // File upload handling
    function handleFileUpload($input) {
        const $wrapper = $input.closest('.rwmb-field');
        const $fileList = $wrapper.find('.rwmb-file-list');
        const files = $input[0].files;
        
        if (!files.length) {
            return;
        }

        // Clear previous files
        $fileList.empty();

        // Add new files
        Array.from(files).forEach(file => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const fileItem = `
                    <div class="rwmb-file-item">
                        <div class="rwmb-file-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24">
                                <path d="M14 2H6C4.9 2 4 2.9 4 4V20C4 21.1 4.9 22 6 22H18C19.1 22 20 21.1 20 20V8L14 2Z"/>
                            </svg>
                        </div>
                        <div class="rwmb-file-name">${file.name}</div>
                        <div class="rwmb-file-remove">×</div>
                    </div>
                `;
                $fileList.append(fileItem);
            };
            reader.readAsDataURL(file);
        });
    }

    // Group field handling
    function handleGroupField($group) {
        const $addButton = $group.find('.rwmb-group-add');
        const $clones = $group.find('.rwmb-group-clone');
        const groupName = $group.data('group-name');

        // Add new group
        $addButton.on('click', function(e) {
            e.preventDefault();
            const $clone = $clones.first().clone();
            $clone.find('input, textarea, select').val('');
            $clone.insertBefore($addButton);
        });

        // Remove group
        $group.on('click', '.rwmb-group-remove', function() {
            const $clone = $(this).closest('.rwmb-group-clone');
            if ($clones.length > 1) {
                $clone.remove();
            } else {
                $clone.find('input, textarea, select').val('');
            }
        });
    }

    // Progress bar handling
    function updateProgress($form) {
        const $steps = $('.piper-privacy-progress-step');
        const totalSteps = $steps.length;
        let currentStep = 1;

        // Find current step based on visible fields
        $form.find('.rwmb-field').each(function() {
            if ($(this).is(':visible')) {
                const stepIndex = Math.floor((currentStep / totalSteps) * 100);
                $steps.removeClass('active completed');
                $steps.eq(currentStep - 1).addClass('active');
                $steps.slice(0, currentStep - 1).addClass('completed');
                return false;
            }
            currentStep++;
        });
    }

    // Initialize form
    function initForm() {
        const $form = $('.rwmb-form');

        if (!$form.length) {
            return;
        }

        // Add form validation
        $form.on('submit', function(e) {
            if (!validateForm($(this))) {
                e.preventDefault();
            }
        });

        // Handle file uploads
        $form.on('change', 'input[type="file"]', function() {
            handleFileUpload($(this));
        });

        // Handle group fields
        $('.rwmb-group-wrapper').each(function() {
            handleGroupField($(this));
        });

        // Handle progress bar
        if ($('.piper-privacy-progress').length) {
            updateProgress($form);
            $form.on('change', 'input, textarea, select', function() {
                updateProgress($form);
            });
        }

        // Handle conditional fields
        $form.on('change', '[data-depends]', function() {
            const $field = $(this);
            const depends = $field.data('depends');
            const $target = $form.find(`[name="${depends.field}"]`);
            const value = $target.val();

            if (depends.value === value) {
                $field.closest('.rwmb-field').show();
            } else {
                $field.closest('.rwmb-field').hide();
            }
        });
    }

    // Initialize on document ready
    $(document).ready(function() {
        initForm();
    });

})(jQuery);
