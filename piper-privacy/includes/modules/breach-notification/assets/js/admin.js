/**
 * Breach Notification Admin JavaScript
 *
 * @package     PiperPrivacy
 * @subpackage  Modules\BreachNotification
 */

/* global jQuery, _, ppBreach, ppBreachNotification */

(function($) {
    'use strict';

    // Breach list view
    var BreachListView = {
        init: function() {
            this.container = $('#pp-breach-list');
            this.template = _.template($('#pp-breach-list-template').html());
            this.setupEventListeners();
            this.loadBreaches();
        },

        setupEventListeners: function() {
            // Filter changes
            $('#pp-severity-filter, #pp-status-filter').on('change', this.loadBreaches.bind(this));
            
            // Search input
            $('#pp-search-breaches').on('input', _.debounce(this.loadBreaches.bind(this), 300));

            // Add new breach
            $('#pp-add-breach').on('click', function(e) {
                e.preventDefault();
                BreachFormView.show();
            });

            // View breach
            this.container.on('click', '.pp-view-breach', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                BreachSingleView.show(id);
            });

            // Edit breach
            this.container.on('click', '.pp-edit-breach', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                BreachFormView.show(id);
            });

            // Delete breach
            this.container.on('click', '.pp-delete-breach', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                if (confirm(ppBreach.i18n.confirmDelete)) {
                    BreachListView.deleteBreach(id);
                }
            });

            // Send notification
            this.container.on('click', '.pp-send-notification', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                NotificationFormView.show(id);
            });

            // Status change
            this.container.on('change', '.pp-status-action', function() {
                var id = $(this).data('id');
                var status = $(this).val();
                if (status) {
                    BreachListView.updateStatus(id, status);
                }
            });
        },

        loadBreaches: function() {
            var data = {
                severity: $('#pp-severity-filter').val(),
                status: $('#pp-status-filter').val(),
                search: $('#pp-search-breaches').val()
            };

            $.ajax({
                url: ppBreach.apiRoot + '/breaches',
                method: 'GET',
                data: data,
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', ppBreach.nonce);
                },
                success: function(response) {
                    BreachListView.render(response);
                },
                error: function() {
                    alert(ppBreach.i18n.error);
                }
            });
        },

        render: function(breaches) {
            this.container.html(this.template({
                breaches: breaches,
                formatSeverity: function(severity) {
                    return '<span class="pp-severity pp-severity-' + severity + '">' + 
                           severity.charAt(0).toUpperCase() + severity.slice(1) + '</span>';
                },
                formatStatus: function(status) {
                    return '<span class="pp-status pp-status-' + status + '">' + 
                           status.charAt(0).toUpperCase() + status.slice(1) + '</span>';
                },
                formatDate: function(date) {
                    return new Date(date).toLocaleDateString();
                }
            }));
        },

        deleteBreach: function(id) {
            $.ajax({
                url: ppBreach.apiRoot + '/breaches/' + id,
                method: 'DELETE',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', ppBreach.nonce);
                },
                success: function() {
                    BreachListView.loadBreaches();
                },
                error: function() {
                    alert(ppBreach.i18n.error);
                }
            });
        },

        updateStatus: function(id, status) {
            $.ajax({
                url: ppBreach.apiRoot + '/breaches/' + id,
                method: 'POST',
                data: {
                    status: status
                },
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', ppBreach.nonce);
                },
                success: function() {
                    BreachListView.loadBreaches();
                },
                error: function() {
                    alert(ppBreach.i18n.error);
                }
            });
        }
    };

    // Breach form view
    var BreachFormView = {
        init: function() {
            this.modal = $('#pp-breach-modal');
            this.container = $('#pp-breach-form');
            this.template = _.template($('#pp-breach-form-template').html());
            this.setupEventListeners();
        },

        setupEventListeners: function() {
            // Close modal
            this.modal.on('click', '.pp-modal-close', function() {
                BreachFormView.hide();
            });

            // Form submission
            this.container.on('submit', 'form', function(e) {
                e.preventDefault();
                BreachFormView.save($(this));
            });
        },

        show: function(id) {
            if (id) {
                // Load breach data for editing
                $.ajax({
                    url: ppBreach.apiRoot + '/breaches/' + id,
                    method: 'GET',
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', ppBreach.nonce);
                    },
                    success: function(breach) {
                        BreachFormView.render(breach);
                    },
                    error: function() {
                        alert(ppBreach.i18n.error);
                    }
                });
            } else {
                // Show empty form for new breach
                this.render();
            }
        },

        hide: function() {
            this.modal.hide();
        },

        render: function(breach) {
            this.container.html(this.template({
                breach: breach,
                formatDateInput: function(date) {
                    return date ? new Date(date).toISOString().slice(0, 16) : '';
                }
            }));
            this.modal.show();

            // Initialize select2 for multiple selects
            this.container.find('select[multiple]').select2();
        },

        save: function($form) {
            var data = $form.serializeArray();
            var id = $form.find('[name="breach_id"]').val();
            var method = id ? 'PUT' : 'POST';
            var url = ppBreach.apiRoot + '/breaches';

            if (id) {
                url += '/' + id;
            }

            $.ajax({
                url: url,
                method: method,
                data: data,
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', ppBreach.nonce);
                },
                success: function() {
                    BreachFormView.hide();
                    BreachListView.loadBreaches();
                },
                error: function() {
                    alert(ppBreach.i18n.error);
                }
            });
        }
    };

    // Breach single view
    var BreachSingleView = {
        init: function() {
            this.modal = $('#pp-breach-single-modal');
            this.container = $('#pp-breach-single');
            this.template = _.template($('#pp-breach-single-template').html());
            this.setupEventListeners();
        },

        setupEventListeners: function() {
            // Close modal
            this.modal.on('click', '.pp-modal-close, .pp-close-breach', function() {
                BreachSingleView.hide();
            });

            // Edit breach
            this.container.on('click', '.pp-edit-breach', function() {
                var id = $(this).data('id');
                BreachSingleView.hide();
                BreachFormView.show(id);
            });

            // Delete breach
            this.container.on('click', '.pp-delete-breach', function() {
                var id = $(this).data('id');
                if (confirm(ppBreach.i18n.confirmDelete)) {
                    BreachListView.deleteBreach(id);
                    BreachSingleView.hide();
                }
            });

            // Send notification
            this.container.on('click', '.pp-send-notification', function() {
                var id = $(this).data('id');
                NotificationFormView.show(id);
            });
        },

        show: function(id) {
            $.ajax({
                url: ppBreach.apiRoot + '/breaches/' + id,
                method: 'GET',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', ppBreach.nonce);
                },
                success: function(breach) {
                    BreachSingleView.render(breach);
                },
                error: function() {
                    alert(ppBreach.i18n.error);
                }
            });
        },

        hide: function() {
            this.modal.hide();
        },

        render: function(breach) {
            this.container.html(this.template({
                breach: breach,
                formatDate: function(date) {
                    return new Date(date).toLocaleString();
                }
            }));
            this.modal.show();
        }
    };

    // Notification form view
    var NotificationFormView = {
        init: function() {
            this.modal = $('#pp-notification-modal');
            this.container = $('#pp-notification-form');
            this.template = _.template($('#pp-notification-form-template').html());
            this.setupEventListeners();
        },

        setupEventListeners: function() {
            // Close modal
            this.modal.on('click', '.pp-modal-close', function() {
                NotificationFormView.hide();
            });

            // Form submission
            this.container.on('submit', 'form', function(e) {
                e.preventDefault();
                NotificationFormView.save($(this));
            });
        },

        show: function(id) {
            $.ajax({
                url: ppBreach.apiRoot + '/breaches/' + id,
                method: 'GET',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', ppBreach.nonce);
                },
                success: function(breach) {
                    NotificationFormView.render(breach);
                },
                error: function() {
                    alert(ppBreach.i18n.error);
                }
            });
        },

        hide: function() {
            this.modal.hide();
        },

        render: function(breach) {
            this.container.html(this.template({
                breach: breach,
                formatDateInput: function(date) {
                    return new Date(date).toISOString().slice(0, 16);
                }
            }));
            this.modal.show();

            // Initialize select2 for recipients
            this.container.find('#recipients').select2();
        },

        save: function($form) {
            var data = $form.serializeArray();
            var id = $form.find('[name="breach_id"]').val();

            $.ajax({
                url: ppBreach.apiRoot + '/breaches/' + id + '/notifications',
                method: 'POST',
                data: data,
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', ppBreach.nonce);
                },
                success: function() {
                    NotificationFormView.hide();
                    if (BreachSingleView.isVisible()) {
                        BreachSingleView.show(id);
                    } else {
                        BreachListView.loadBreaches();
                    }
                },
                error: function() {
                    alert(ppBreach.i18n.error);
                }
            });
        }
    };

    const PiperPrivacyBreachNotification = {
        init: function() {
            this.bindEvents();
            this.initRiskAssessment();
        },

        bindEvents: function() {
            $(document).on('click', '.pp-assess-risk', this.handleRiskAssessment.bind(this));
            $(document).on('click', '.pp-analyze-compliance', this.handleComplianceAnalysis.bind(this));
        },

        initRiskAssessment: function() {
            // Auto-run risk assessment for new breaches
            if ($('#pp-breach-new').length) {
                this.assessRisk();
            }
        },

        handleRiskAssessment: function(e) {
            e.preventDefault();
            this.assessRisk();
        },

        handleComplianceAnalysis: function(e) {
            e.preventDefault();
            this.analyzeCompliance();
        },

        assessRisk: function() {
            const $container = $('#pp-risk-assessment');
            const breachId = $container.data('breach-id');

            if (!breachId) {
                return;
            }

            $container.addClass('loading');
            this.showSpinner($container);

            $.ajax({
                url: ppBreachNotification.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'pp_assess_breach_risk',
                    breach_id: breachId,
                    nonce: ppBreachNotification.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.updateRiskAssessment(response.data);
                    } else {
                        this.showError(response.data);
                    }
                },
                error: () => {
                    this.showError(ppBreachNotification.i18n.error);
                },
                complete: () => {
                    $container.removeClass('loading');
                    this.hideSpinner($container);
                }
            });
        },

        analyzeCompliance: function() {
            const $container = $('#pp-compliance-analysis');
            const breachId = $container.data('breach-id');

            if (!breachId) {
                return;
            }

            $container.addClass('loading');
            this.showSpinner($container);

            $.ajax({
                url: ppBreachNotification.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'pp_analyze_compliance',
                    breach_id: breachId,
                    nonce: ppBreachNotification.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.updateComplianceAnalysis(response.data);
                    } else {
                        this.showError(response.data);
                    }
                },
                error: () => {
                    this.showError(ppBreachNotification.i18n.error);
                },
                complete: () => {
                    $container.removeClass('loading');
                    this.hideSpinner($container);
                }
            });
        },

        updateRiskAssessment: function(assessment) {
            const $container = $('#pp-risk-assessment');
            const $score = $container.find('.pp-risk-score');
            const $severity = $container.find('.pp-risk-severity');
            const $factors = $container.find('.pp-risk-factors');
            const $requirements = $container.find('.pp-notification-requirements');
            const $recommendations = $container.find('.pp-recommendations');
            const $deadlines = $container.find('.pp-deadlines');

            // Update score and severity
            $score.text(assessment.score);
            $severity.text(assessment.severity)
                .removeClass('low medium high critical')
                .addClass(assessment.severity);

            // Update risk factors
            let factorsHtml = '';
            Object.entries(assessment.factors).forEach(([factor, data]) => {
                factorsHtml += `
                    <div class="pp-risk-factor">
                        <h4>${this.formatLabel(factor)}</h4>
                        <div class="pp-factor-score">${data.score}</div>
                        <div class="pp-factor-weight">Weight: ${data.weight * 100}%</div>
                        <div class="pp-factor-details">${this.formatDetails(data.details)}</div>
                    </div>
                `;
            });
            $factors.html(factorsHtml);

            // Update notification requirements
            let requirementsHtml = '';
            Object.entries(assessment.notification_requirements).forEach(([type, required]) => {
                requirementsHtml += `
                    <div class="pp-requirement ${required ? 'required' : 'not-required'}">
                        <span class="dashicons ${required ? 'dashicons-yes' : 'dashicons-no'}"></span>
                        ${this.formatLabel(type)}
                    </div>
                `;
            });
            $requirements.html(requirementsHtml);

            // Update recommendations
            let recommendationsHtml = '';
            Object.entries(assessment.recommendations).forEach(([category, items]) => {
                recommendationsHtml += `
                    <div class="pp-recommendation-category">
                        <h4>${this.formatLabel(category)}</h4>
                        <ul>
                            ${items.map(item => `<li>${item}</li>`).join('')}
                        </ul>
                    </div>
                `;
            });
            $recommendations.html(recommendationsHtml);

            // Update deadlines
            let deadlinesHtml = '';
            Object.entries(assessment.deadlines).forEach(([type, deadline]) => {
                deadlinesHtml += `
                    <div class="pp-deadline">
                        <span class="pp-deadline-type">${this.formatLabel(type)}</span>
                        <span class="pp-deadline-date">${this.formatDate(deadline)}</span>
                    </div>
                `;
            });
            $deadlines.html(deadlinesHtml);

            // Show compliance analysis button if not already analyzed
            if (!$('#pp-compliance-analysis').hasClass('analyzed')) {
                $('.pp-analyze-compliance').removeClass('hidden');
            }
        },

        updateComplianceAnalysis: function(compliance) {
            const $container = $('#pp-compliance-analysis');
            const $frameworks = $container.find('.pp-frameworks');
            const $summary = $container.find('.pp-compliance-summary');
            const $documentation = $container.find('.pp-documentation-requirements');

            // Update frameworks
            let frameworksHtml = '';
            Object.entries(compliance.frameworks).forEach(([id, framework]) => {
                frameworksHtml += `
                    <div class="pp-framework">
                        <h4>${framework.name}</h4>
                        ${this.renderNotificationRequirements(framework.notifications)}
                        ${this.renderDocumentationRequirements(framework.documentation)}
                    </div>
                `;
            });
            $frameworks.html(frameworksHtml);

            // Update summary
            let summaryHtml = `
                <div class="pp-summary-item ${compliance.summary.authority_notification ? 'required' : ''}">
                    <span class="dashicons ${compliance.summary.authority_notification ? 'dashicons-yes' : 'dashicons-no'}"></span>
                    Authority Notification Required
                </div>
                <div class="pp-summary-item ${compliance.summary.individual_notification ? 'required' : ''}">
                    <span class="dashicons ${compliance.summary.individual_notification ? 'dashicons-yes' : 'dashicons-no'}"></span>
                    Individual Notification Required
                </div>
            `;
            if (compliance.summary.shortest_deadline) {
                summaryHtml += `
                    <div class="pp-summary-item">
                        <span class="dashicons dashicons-clock"></span>
                        Shortest Deadline: ${this.formatDate(compliance.summary.shortest_deadline)}
                    </div>
                `;
            }
            $summary.html(summaryHtml);

            // Update documentation requirements
            let documentationHtml = `
                <h4>Required Documentation Elements</h4>
                <ul>
                    ${compliance.documentation.required_elements.map(element => `
                        <li>${this.formatLabel(element)}</li>
                    `).join('')}
                </ul>
                <h4>Retention Periods</h4>
                ${Object.entries(compliance.documentation.retention_periods).map(([framework, period]) => `
                    <div class="pp-retention-period">
                        <span class="pp-framework-name">${framework}:</span>
                        <span class="pp-retention-time">${period}</span>
                    </div>
                `).join('')}
            `;
            $documentation.html(documentationHtml);

            $container.addClass('analyzed');
        },

        renderNotificationRequirements: function(notifications) {
            if (!notifications) {
                return '';
            }

            let html = '<div class="pp-notification-requirements">';
            Object.entries(notifications).forEach(([type, data]) => {
                html += `
                    <div class="pp-notification ${data.required ? 'required' : 'not-required'}">
                        <h5>${this.formatLabel(type)} Notification</h5>
                        <div class="pp-notification-deadline">Deadline: ${data.deadline}</div>
                        ${this.renderExceptions(data.exceptions_met)}
                    </div>
                `;
            });
            html += '</div>';
            return html;
        },

        renderDocumentationRequirements: function(documentation) {
            if (!documentation || !documentation.elements) {
                return '';
            }

            return `
                <div class="pp-documentation">
                    <h5>Required Documentation</h5>
                    <ul>
                        ${documentation.elements.map(element => `
                            <li>${this.formatLabel(element)}</li>
                        `).join('')}
                    </ul>
                    ${documentation.retention ? `
                        <div class="pp-retention">
                            Retention Period: ${documentation.retention}
                        </div>
                    ` : ''}
                </div>
            `;
        },

        renderExceptions: function(exceptions) {
            if (!exceptions || Object.keys(exceptions).length === 0) {
                return '';
            }

            return `
                <div class="pp-exceptions">
                    <h6>Exceptions Applied:</h6>
                    <ul>
                        ${Object.entries(exceptions).map(([key, description]) => `
                            <li>${description}</li>
                        `).join('')}
                    </ul>
                </div>
            `;
        },

        formatLabel: function(str) {
            return str.split('_')
                .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                .join(' ');
        },

        formatDate: function(dateStr) {
            return new Date(dateStr).toLocaleString();
        },

        formatDetails: function(details) {
            if (typeof details === 'object') {
                return Object.entries(details)
                    .map(([key, value]) => `${this.formatLabel(key)}: ${value}`)
                    .join('<br>');
            }
            return details;
        },

        showSpinner: function($container) {
            $container.find('.spinner').addClass('is-active');
        },

        hideSpinner: function($container) {
            $container.find('.spinner').removeClass('is-active');
        },

        showError: function(message) {
            const $notice = $('<div class="notice notice-error is-dismissible"><p></p></div>');
            $notice.find('p').text(message);
            $('#pp-notices').html($notice);
        }
    };

    // Document upload handling
    function initDocumentUpload() {
        $('.pp-document-upload').on('change', 'input[type="file"]', function(e) {
            var $input = $(this);
            var file = e.target.files[0];
            var $form = $input.closest('form');
            var formData = new FormData();

            formData.append('action', 'pp_upload_document');
            formData.append('nonce', ppBreachNotification.nonce);
            formData.append('file', file);
            formData.append('breach_id', $form.find('[name="breach_id"]').val());
            formData.append('document_type', $form.find('[name="document_type"]').val());

            $.ajax({
                url: ppBreachNotification.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $form.addClass('loading');
                },
                success: function(response) {
                    if (response.success) {
                        updateDocumentList(response.data);
                    } else {
                        alert(ppBreachNotification.i18n.uploadError);
                    }
                },
                error: function() {
                    alert(ppBreachNotification.i18n.uploadError);
                },
                complete: function() {
                    $form.removeClass('loading');
                    $input.val('');
                }
            });
        });
    }

    // Export handling
    function initExport() {
        $('.pp-export-button').on('click', function(e) {
            e.preventDefault();
            var $button = $(this);
            var breachId = $button.data('breach-id');
            var format = $button.data('format');

            $.ajax({
                url: ppBreachNotification.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'pp_export_breach',
                    nonce: ppBreachNotification.nonce,
                    breach_id: breachId,
                    format: format
                },
                beforeSend: function() {
                    $button.prop('disabled', true);
                },
                success: function(response) {
                    if (response.success) {
                        window.location.href = response.data.download_url;
                    } else {
                        alert(ppBreachNotification.i18n.exportError);
                    }
                },
                error: function() {
                    alert(ppBreachNotification.i18n.exportError);
                },
                complete: function() {
                    $button.prop('disabled', false);
                }
            });
        });

        $('.pp-export-documents').on('click', function(e) {
            e.preventDefault();
            var $button = $(this);
            var breachId = $button.data('breach-id');

            $.ajax({
                url: ppBreachNotification.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'pp_export_documents',
                    nonce: ppBreachNotification.nonce,
                    breach_id: breachId
                },
                beforeSend: function() {
                    $button.prop('disabled', true);
                },
                success: function(response) {
                    if (response.success) {
                        window.location.href = response.data.download_url;
                    } else {
                        alert(ppBreachNotification.i18n.exportError);
                    }
                },
                error: function() {
                    alert(ppBreachNotification.i18n.exportError);
                },
                complete: function() {
                    $button.prop('disabled', false);
                }
            });
        });
    }

    // Notification handling
    function initNotifications() {
        function updateNotifications() {
            $.ajax({
                url: ppBreachNotification.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'pp_get_notifications',
                    nonce: ppBreachNotification.nonce
                },
                success: function(response) {
                    if (response.success) {
                        renderNotifications(response.data);
                    }
                }
            });
        }

        function renderNotifications(notifications) {
            var $container = $('.pp-notifications');
            if (!$container.length) {
                return;
            }

            $container.empty();
            notifications.forEach(function(notification) {
                var $notification = $('<div>')
                    .addClass('pp-notification')
                    .addClass('pp-notification-' + notification.type)
                    .addClass('pp-priority-' + notification.priority);

                $notification.append(
                    $('<div>').addClass('pp-notification-message').text(notification.message)
                );

                var $actions = $('<div>').addClass('pp-notification-actions');
                $actions.append(
                    $('<button>')
                        .addClass('button pp-mark-read')
                        .text('Mark as Read')
                        .data('notification-id', notification.id)
                );

                if (notification.breach_id) {
                    $actions.append(
                        $('<a>')
                            .addClass('button')
                            .attr('href', 'admin.php?page=piper-privacy-breach&action=view&id=' + notification.breach_id)
                            .text('View Breach')
                    );
                }

                $notification.append($actions);
                $container.append($notification);
            });
        }

        $('.pp-notifications').on('click', '.pp-mark-read', function() {
            var $button = $(this);
            var notificationId = $button.data('notification-id');

            $.ajax({
                url: ppBreachNotification.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'pp_mark_notification_read',
                    nonce: ppBreachNotification.nonce,
                    notification_id: notificationId
                },
                success: function(response) {
                    if (response.success) {
                        $button.closest('.pp-notification').fadeOut();
                    }
                }
            });
        });

        // Update notifications every 5 minutes
        updateNotifications();
        setInterval(updateNotifications, 300000);
    }

    // Compliance checking
    function initComplianceChecker() {
        $('.pp-check-compliance').on('click', function(e) {
            e.preventDefault();
            var $button = $(this);
            var breachId = $button.data('breach-id');

            $.ajax({
                url: ppBreachNotification.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'pp_check_compliance',
                    nonce: ppBreachNotification.nonce,
                    breach_id: breachId
                },
                beforeSend: function() {
                    $button.prop('disabled', true);
                    $('#pp-compliance-analysis').addClass('loading');
                },
                success: function(response) {
                    if (response.success) {
                        updateComplianceAnalysis(response.data);
                    } else {
                        alert(ppBreachNotification.i18n.complianceError);
                    }
                },
                error: function() {
                    alert(ppBreachNotification.i18n.complianceError);
                },
                complete: function() {
                    $button.prop('disabled', false);
                    $('#pp-compliance-analysis').removeClass('loading');
                }
            });
        });
    }

    // Document list updates
    function updateDocumentList(document) {
        var $list = $('.pp-document-list');
        if (!$list.length) {
            return;
        }

        var $item = $('<div>')
            .addClass('pp-document-item')
            .addClass('pp-document-type-' + document.type);

        $item.append(
            $('<span>')
                .addClass('pp-document-title')
                .append(
                    $('<a>')
                        .attr('href', document.file_url)
                        .attr('target', '_blank')
                        .text(document.file_name)
                )
        );

        $item.append(
            $('<span>')
                .addClass('pp-document-type')
                .text(document.type)
        );

        var $actions = $('<div>').addClass('pp-document-actions');
        $actions.append(
            $('<button>')
                .addClass('button pp-delete-document')
                .text('Delete')
                .data('document-id', document.id)
        );

        $item.append($actions);
        $list.append($item);
    }

    // Initialize all features
    $(document).ready(function() {
        BreachListView.init();
        BreachFormView.init();
        BreachSingleView.init();
        NotificationFormView.init();
        PiperPrivacyBreachNotification.init();
        initDocumentUpload();
        initExport();
        initNotifications();
        initComplianceChecker();

        // Delete document handling
        $('.pp-document-list').on('click', '.pp-delete-document', function() {
            if (!confirm(ppBreachNotification.i18n.confirmDelete)) {
                return;
            }

            var $button = $(this);
            var documentId = $button.data('document-id');

            $.ajax({
                url: ppBreachNotification.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'pp_delete_document',
                    nonce: ppBreachNotification.nonce,
                    document_id: documentId
                },
                beforeSend: function() {
                    $button.prop('disabled', true);
                },
                success: function(response) {
                    if (response.success) {
                        $button.closest('.pp-document-item').fadeOut(function() {
                            $(this).remove();
                        });
                    }
                },
                complete: function() {
                    $button.prop('disabled', false);
                }
            });
        });
    });
})(jQuery);
