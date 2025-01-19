/* global jQuery, ppConsent */
(function($) {
    'use strict';

    const ConsentForm = {
        init() {
            this.bindEvents();
        },

        bindEvents() {
            $('.pp-consent-form form').on('submit', this.handleConsent);
            $('.pp-withdraw-consent').on('click', this.handleWithdraw);
        },

        async handleConsent(e) {
            e.preventDefault();
            const form = $(this);
            const container = form.closest('.pp-consent-form');
            const messageEl = container.find('.pp-consent-message');

            // Get form data
            const data = {
                consent_type: form.find('input[name="consent_type"]').val(),
                user_id: form.find('input[name="user_id"]').val(),
                status: form.find('input[name="status"]').val(),
            };

            try {
                const response = await $.ajax({
                    url: ppConsent.ajaxUrl,
                    method: 'POST',
                    data: {
                        action: 'pp_save_consent',
                        nonce: ppConsent.nonce,
                        ...data,
                    },
                });

                if (response.success) {
                    messageEl.removeClass('error').addClass('success')
                        .html(ppConsent.i18n.success).show();
                    
                    // Update form state
                    form.find('input[type="checkbox"]').prop('disabled', true);
                    form.find('button[type="submit"]').hide();
                    
                    // Add withdraw button
                    const withdrawBtn = $('<button>', {
                        type: 'button',
                        class: 'button pp-withdraw-consent',
                        text: 'Withdraw Consent',
                    });
                    form.find('.pp-form-actions').append(withdrawBtn);
                } else {
                    throw new Error(response.data.message);
                }
            } catch (error) {
                messageEl.removeClass('success').addClass('error')
                    .html(error.message || ppConsent.i18n.error).show();
            }
        },

        async handleWithdraw(e) {
            e.preventDefault();
            const button = $(this);
            const container = button.closest('.pp-consent-form');
            const messageEl = container.find('.pp-consent-message');
            const consentType = container.data('type');

            if (!confirm('Are you sure you want to withdraw your consent?')) {
                return;
            }

            try {
                const response = await $.ajax({
                    url: ppConsent.ajaxUrl,
                    method: 'POST',
                    data: {
                        action: 'pp_withdraw_consent',
                        nonce: ppConsent.nonce,
                        consent_type: consentType,
                    },
                });

                if (response.success) {
                    // Reload the page to show updated consent status
                    window.location.reload();
                } else {
                    throw new Error(response.data.message);
                }
            } catch (error) {
                messageEl.removeClass('success').addClass('error')
                    .html(error.message || ppConsent.i18n.error).show();
            }
        },
    };

    $(document).ready(() => {
        ConsentForm.init();
    });
})(jQuery);
