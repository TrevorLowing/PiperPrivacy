/* global jQuery, wp, ppConsent */
(function($) {
    'use strict';

    const ConsentManager = {
        init() {
            this.bindEvents();
            this.loadConsentTypes();
            this.loadConsents();
        },

        bindEvents() {
            // Consent Type Modal
            $('#pp-new-consent-type').on('click', this.showConsentTypeModal);
            $('.pp-modal-close, .pp-modal-cancel').on('click', this.hideConsentTypeModal);
            $('#pp-consent-type-form').on('submit', this.saveConsentType);

            // Consent Actions
            $(document).on('click', '.pp-view-consent', this.viewConsent);
            $(document).on('click', '.pp-withdraw-consent', this.withdrawConsent);
            $(document).on('click', '.pp-delete-consent', this.deleteConsent);
            $(document).on('click', '.pp-close-single', this.closeSingleView);
        },

        showConsentTypeModal(e) {
            e.preventDefault();
            $('#pp-consent-type-modal').show();
        },

        hideConsentTypeModal() {
            $('#pp-consent-type-modal').hide();
            $('#pp-consent-type-form')[0].reset();
        },

        async saveConsentType(e) {
            e.preventDefault();
            const form = $(this);
            const data = {
                name: form.find('#consent_type_name').val(),
                description: form.find('#consent_type_description').val(),
                expiry: form.find('#consent_type_expiry').val(),
                required: form.find('input[name="required"]').is(':checked'),
            };

            try {
                await wp.apiRequest({
                    path: '/piper-privacy/v1/consent-types',
                    method: 'POST',
                    data,
                });

                ConsentManager.hideConsentTypeModal();
                ConsentManager.loadConsentTypes();
            } catch (error) {
                alert(error.message || 'Failed to save consent type');
            }
        },

        async loadConsentTypes() {
            try {
                const types = await wp.apiRequest({
                    path: '/piper-privacy/v1/consent-types',
                    method: 'GET',
                });

                const list = $('#pp-consent-types-list');
                list.empty();

                types.forEach(type => {
                    list.append(`
                        <div class="pp-consent-type-item">
                            <h3>${type.name}</h3>
                            <p>${type.description}</p>
                            <div class="row-actions">
                                <span class="edit">
                                    <a href="#" class="pp-edit-type" data-id="${type.id}">Edit</a> |
                                </span>
                                <span class="delete">
                                    <a href="#" class="pp-delete-type" data-id="${type.id}">Delete</a>
                                </span>
                            </div>
                        </div>
                    `);
                });
            } catch (error) {
                console.error('Failed to load consent types:', error);
            }
        },

        async loadConsents() {
            try {
                const consents = await wp.apiRequest({
                    path: '/piper-privacy/v1/consents',
                    method: 'GET',
                });

                const list = $('#pp-consent-list');
                list.empty();

                if (consents.length === 0) {
                    list.html('<div class="pp-no-items"><p>No consent records found.</p></div>');
                    return;
                }

                // Render consent list template
                // This would be better handled by a template, but for simplicity we'll do it here
                const table = $('<table class="wp-list-table widefat fixed striped">');
                table.append(`
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Expiry</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                `);

                const tbody = $('<tbody>');
                consents.forEach(consent => {
                    tbody.append(`
                        <tr>
                            <td>
                                ${consent.user_name}<br>
                                <small>${consent.user_email}</small>
                            </td>
                            <td>${consent.consent_type}</td>
                            <td>
                                <span class="pp-status pp-status-${consent.status}">
                                    ${consent.status}
                                </span>
                            </td>
                            <td>${consent.created_at}</td>
                            <td>${consent.expiry_date || 'No expiry'}</td>
                            <td>
                                <div class="row-actions">
                                    <span class="view">
                                        <a href="#" class="pp-view-consent" data-id="${consent.id}">View</a> |
                                    </span>
                                    ${consent.status !== 'withdrawn' ? `
                                        <span class="withdraw">
                                            <a href="#" class="pp-withdraw-consent" data-id="${consent.id}">Withdraw</a> |
                                        </span>
                                    ` : ''}
                                    <span class="delete">
                                        <a href="#" class="pp-delete-consent" data-id="${consent.id}">Delete</a>
                                    </span>
                                </div>
                            </td>
                        </tr>
                    `);
                });

                table.append(tbody);
                list.append(table);
            } catch (error) {
                console.error('Failed to load consents:', error);
            }
        },

        async viewConsent(e) {
            e.preventDefault();
            const id = $(this).data('id');

            try {
                const consent = await wp.apiRequest({
                    path: `/piper-privacy/v1/consents/${id}`,
                    method: 'GET',
                });

                $('#pp-consent-list').hide();
                $('#pp-consent-single').html(consent.html).show();
            } catch (error) {
                alert('Failed to load consent details');
            }
        },

        async withdrawConsent(e) {
            e.preventDefault();
            if (!confirm('Are you sure you want to withdraw this consent?')) {
                return;
            }

            const id = $(this).data('id');

            try {
                await wp.apiRequest({
                    path: `/piper-privacy/v1/consents/${id}/withdraw`,
                    method: 'POST',
                });

                ConsentManager.loadConsents();
                $('#pp-consent-single').hide();
                $('#pp-consent-list').show();
            } catch (error) {
                alert('Failed to withdraw consent');
            }
        },

        async deleteConsent(e) {
            e.preventDefault();
            if (!confirm('Are you sure you want to delete this consent record?')) {
                return;
            }

            const id = $(this).data('id');

            try {
                await wp.apiRequest({
                    path: `/piper-privacy/v1/consents/${id}`,
                    method: 'DELETE',
                });

                ConsentManager.loadConsents();
                $('#pp-consent-single').hide();
                $('#pp-consent-list').show();
            } catch (error) {
                alert('Failed to delete consent record');
            }
        },

        closeSingleView(e) {
            e.preventDefault();
            $('#pp-consent-single').hide();
            $('#pp-consent-list').show();
        },
    };

    $(document).ready(() => {
        ConsentManager.init();
    });
})(jQuery);
