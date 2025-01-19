/* global jQuery */
(function($) {
    'use strict';

    class UIEnhancements {
        constructor() {
            this.init();
        }

        init() {
            this.initGroupFields();
            this.initFileUploads();
            this.initTooltips();
            this.initFormValidation();
            this.initSubmitHandling();
            this.initConditionalFields();
            this.initCharacterCount();
        }

        initGroupFields() {
            // Handle dynamic group field addition
            $(document).on('click', '.rwmb-group-add', function() {
                const $wrapper = $(this).closest('.rwmb-group-wrapper');
                const $clone = $wrapper.find('.rwmb-group-clone').first().clone();
                const index = $wrapper.find('.rwmb-group-clone').length;

                // Reset form fields
                $clone.find('input, textarea, select').each(function() {
                    const $field = $(this);
                    const name = $field.attr('name');
                    if (name) {
                        $field.attr('name', name.replace('[0]', `[${index}]`));
                        $field.val('');
                    }
                });

                // Add clone with animation
                $clone.hide().insertBefore($(this)).slideDown(300);
            });

            // Handle group field removal
            $(document).on('click', '.rwmb-group-remove', function() {
                const $clone = $(this).closest('.rwmb-group-clone');
                $clone.slideUp(300, function() {
                    $clone.remove();
                });
            });
        }

        initFileUploads() {
            $('.rwmb-input input[type="file"]').each(function() {
                const $input = $(this);
                const $wrapper = $input.closest('.rwmb-input');
                
                // Add file name display
                $wrapper.append('<div class="file-name"></div>');
                
                $input.on('change', function() {
                    const fileName = this.files[0]?.name || '';
                    $wrapper.find('.file-name').text(fileName);
                });
            });
        }

        initTooltips() {
            // Add tooltip triggers
            $('.rwmb-label label').each(function() {
                const $label = $(this);
                const description = $label.siblings('.description').text();
                
                if (description) {
                    $label.append(`
                        <span class="tooltip-trigger" data-tooltip="${description}">
                            <svg viewBox="0 0 24 24" width="16" height="16">
                                <circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="2"/>
                                <text x="12" y="16" text-anchor="middle" fill="currentColor" style="font-size: 14px;">?</text>
                            </svg>
                        </span>
                    `);
                }
            });
        }

        initFormValidation() {
            // Real-time validation
            $('.rwmb-input input, .rwmb-input textarea, .rwmb-input select').on('blur', function() {
                const $field = $(this);
                const $wrapper = $field.closest('.rwmb-field');
                
                if ($field.prop('required') && !$field.val()) {
                    $wrapper.addClass('has-error');
                    if (!$wrapper.find('.rwmb-error').length) {
                        $wrapper.append('<div class="rwmb-error">This field is required.</div>');
                    }
                } else {
                    $wrapper.removeClass('has-error');
                    $wrapper.find('.rwmb-error').remove();
                }
            });
        }

        initSubmitHandling() {
            $('.rwmb-form').on('submit', function() {
                const $form = $(this);
                
                // Add loading state
                $form.addClass('is-submitting');
                
                // Simulate form submission delay (remove in production)
                setTimeout(() => {
                    $form.removeClass('is-submitting');
                }, 2000);
            });
        }

        initConditionalFields() {
            // Handle conditional field visibility
            $('[data-depends-on]').each(function() {
                const $dependent = $(this);
                const controlField = $dependent.data('depends-on');
                const $control = $(`[name="${controlField}"]`);
                const expectedValue = $dependent.data('depends-value');

                function updateVisibility() {
                    const currentValue = $control.val();
                    if (currentValue === expectedValue) {
                        $dependent.slideDown(300);
                    } else {
                        $dependent.slideUp(300);
                    }
                }

                $control.on('change', updateVisibility);
                updateVisibility();
            });
        }

        initCharacterCount() {
            // Add character count to textareas
            $('textarea[maxlength]').each(function() {
                const $textarea = $(this);
                const $wrapper = $textarea.closest('.rwmb-input');
                const maxLength = $textarea.attr('maxlength');
                
                $wrapper.append(`<div class="char-count">0/${maxLength} characters</div>`);
                
                $textarea.on('input', function() {
                    const count = $(this).val().length;
                    $wrapper.find('.char-count').text(`${count}/${maxLength} characters`);
                });
            });
        }
    }

    // Initialize UI enhancements
    $(document).ready(function() {
        new UIEnhancements();
    });

})(jQuery);
