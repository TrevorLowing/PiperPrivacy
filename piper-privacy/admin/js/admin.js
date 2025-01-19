(function($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function() {
        PiperPrivacyAdmin.init();
    });

    // Main admin object
    var PiperPrivacyAdmin = {
        init: function() {
            this.initTabs();
            this.initFilters();
            this.initFormValidation();
            this.initAjaxHandlers();
        },

        // Initialize tab switching
        initTabs: function() {
            $('.nav-tab-wrapper a').on('click', function(e) {
                e.preventDefault();
                var target = $(this).attr('href');
                
                // Update active tab
                $('.nav-tab-wrapper a').removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');
                
                // Show target section
                $('.report-section').hide();
                $(target).show();

                // Update URL hash without scrolling
                if (history.pushState) {
                    history.pushState(null, null, target);
                }
            });

            // Check for hash in URL
            if (window.location.hash) {
                $('a[href="' + window.location.hash + '"]').trigger('click');
            }
        },

        // Initialize filter functionality
        initFilters: function() {
            $('.tablenav select').on('change', function() {
                $(this).closest('form').submit();
            });
        },

        // Initialize form validation
        initFormValidation: function() {
            $('form').on('submit', function(e) {
                var $form = $(this);
                var $required = $form.find('[required]');
                var valid = true;

                $required.each(function() {
                    if (!$(this).val()) {
                        e.preventDefault();
                        valid = false;
                        $(this).addClass('error');
                    } else {
                        $(this).removeClass('error');
                    }
                });

                if (!valid) {
                    alert(piperPrivacyAdmin.i18n.requiredFields);
                }
            });
        },

        // Initialize AJAX handlers
        initAjaxHandlers: function() {
            // Handle workflow transitions
            $('.workflow-action').on('click', function(e) {
                e.preventDefault();
                var $button = $(this);
                var data = {
                    action: 'piper_privacy_workflow_transition',
                    nonce: piperPrivacyAdmin.nonce,
                    post_id: $button.data('post-id'),
                    from_stage: $button.data('from-stage'),
                    to_stage: $button.data('to-stage')
                };

                $.ajax({
                    url: piperPrivacyAdmin.restUrl + '/workflow/transition',
                    method: 'POST',
                    data: data,
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', piperPrivacyAdmin.nonce);
                        $button.prop('disabled', true);
                    },
                    success: function(response) {
                        if (response.success) {
                            window.location.reload();
                        } else {
                            alert(response.message || piperPrivacyAdmin.i18n.error);
                        }
                    },
                    error: function() {
                        alert(piperPrivacyAdmin.i18n.error);
                    },
                    complete: function() {
                        $button.prop('disabled', false);
                    }
                });
            });

            // Handle bulk actions
            $('#doaction, #doaction2').on('click', function(e) {
                var $button = $(this);
                var $bulkAction = $button.prev('select');
                var action = $bulkAction.val();

                if (action === 'delete') {
                    if (!confirm(piperPrivacyAdmin.i18n.confirmDelete)) {
                        e.preventDefault();
                    }
                } else if (action === 'retire') {
                    if (!confirm(piperPrivacyAdmin.i18n.confirmRetire)) {
                        e.preventDefault();
                    }
                }
            });

            // Handle settings updates
            $('#piper-privacy-settings-form').on('submit', function(e) {
                e.preventDefault();
                var $form = $(this);
                var data = $form.serialize();

                $.ajax({
                    url: piperPrivacyAdmin.restUrl + '/settings',
                    method: 'POST',
                    data: data,
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', piperPrivacyAdmin.nonce);
                        $form.find('input[type="submit"]').prop('disabled', true);
                    },
                    success: function(response) {
                        if (response.success) {
                            $('<div class="notice notice-success"><p>' + response.message + '</p></div>')
                                .insertAfter($form.find('h1'))
                                .delay(3000)
                                .fadeOut();
                        } else {
                            $('<div class="notice notice-error"><p>' + response.message + '</p></div>')
                                .insertAfter($form.find('h1'));
                        }
                    },
                    error: function() {
                        alert(piperPrivacyAdmin.i18n.error);
                    },
                    complete: function() {
                        $form.find('input[type="submit"]').prop('disabled', false);
                    }
                });
            });
        }
    };

})(jQuery);
