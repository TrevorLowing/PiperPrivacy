/**
 * Dashboard JavaScript functionality
 */
(function($) {
    'use strict';

    const PiperPrivacyDashboard = {
        /**
         * Initialize dashboard functionality
         */
        init: function() {
            this.setupRefreshTimer();
            this.bindEvents();
            this.loadInitialData();
        },

        /**
         * Setup automatic refresh timer
         */
        setupRefreshTimer: function() {
            setInterval(() => {
                this.refreshDashboardData();
            }, 60000); // Refresh every minute
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            $('.refresh-stats').on('click', (e) => {
                e.preventDefault();
                this.refreshDashboardData();
            });

            $('.action-item .button').on('click', function(e) {
                e.preventDefault();
                const actionUrl = $(this).attr('href');
                PiperPrivacyDashboard.handleActionClick(actionUrl);
            });

            // Handle metric card clicks
            $('.overview-cards .card').on('click', function() {
                const detailUrl = $(this).find('a').attr('href');
                if (detailUrl) {
                    window.location.href = detailUrl;
                }
            });
        },

        /**
         * Load initial dashboard data
         */
        loadInitialData: function() {
            this.loadWorkflowStats();
            this.loadComplianceMetrics();
            this.loadRecentActivity();
            this.loadUpcomingActions();
        },

        /**
         * Refresh all dashboard data
         */
        refreshDashboardData: function() {
            $('.dashboard-grid').addClass('loading');
            
            Promise.all([
                this.loadWorkflowStats(),
                this.loadComplianceMetrics(),
                this.loadRecentActivity(),
                this.loadUpcomingActions()
            ]).then(() => {
                $('.dashboard-grid').removeClass('loading');
                this.showUpdateNotice();
            }).catch(error => {
                this.handleError(error);
            });
        },

        /**
         * Load workflow statistics
         */
        loadWorkflowStats: function() {
            return $.ajax({
                url: piperPrivacyDashboard.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'get_privacy_collection_stats',
                    nonce: piperPrivacyDashboard.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.updateWorkflowStats(response.data);
                    } else {
                        this.handleError(response.data.message);
                    }
                }
            });
        },

        /**
         * Load compliance metrics
         */
        loadComplianceMetrics: function() {
            return $.ajax({
                url: piperPrivacyDashboard.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'get_privacy_workflow_metrics',
                    nonce: piperPrivacyDashboard.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.updateComplianceMetrics(response.data);
                    } else {
                        this.handleError(response.data.message);
                    }
                }
            });
        },

        /**
         * Load recent activity
         */
        loadRecentActivity: function() {
            return $.ajax({
                url: piperPrivacyDashboard.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'get_privacy_recent_activity',
                    nonce: piperPrivacyDashboard.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.updateRecentActivity(response.data);
                    } else {
                        this.handleError(response.data.message);
                    }
                }
            });
        },

        /**
         * Load upcoming actions
         */
        loadUpcomingActions: function() {
            return $.ajax({
                url: piperPrivacyDashboard.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'get_privacy_upcoming_actions',
                    nonce: piperPrivacyDashboard.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.updateUpcomingActions(response.data);
                    } else {
                        this.handleError(response.data.message);
                    }
                }
            });
        },

        /**
         * Update workflow statistics display
         */
        updateWorkflowStats: function(stats) {
            Object.keys(stats).forEach(key => {
                const $stat = $(`.card.${key} .stat`);
                if ($stat.length) {
                    this.animateNumber($stat, stats[key]);
                }
            });
        },

        /**
         * Update compliance metrics display
         */
        updateComplianceMetrics: function(metrics) {
            Object.keys(metrics).forEach(key => {
                const $progress = $(`.metric.${key} .progress-bar`);
                if ($progress.length) {
                    $progress.css('width', metrics[key] + '%')
                            .find('span').text(metrics[key] + '%');
                }
            });
        },

        /**
         * Update recent activity list
         */
        updateRecentActivity: function(activities) {
            const $list = $('.activity-list');
            if (!activities.length) {
                $list.html('<p class="no-data">' + piperPrivacyDashboard.i18n.noData + '</p>');
                return;
            }

            const html = activities.map(activity => this.getActivityHTML(activity)).join('');
            $list.html(html);
        },

        /**
         * Update upcoming actions list
         */
        updateUpcomingActions: function(actions) {
            const $list = $('.actions-list');
            if (!actions.length) {
                $list.html('<p class="no-data">' + piperPrivacyDashboard.i18n.noData + '</p>');
                return;
            }

            const html = actions.map(action => this.getActionHTML(action)).join('');
            $list.html(html);
        },

        /**
         * Generate activity HTML
         */
        getActivityHTML: function(activity) {
            return `
                <li class="activity-item type-${activity.type}">
                    <div class="activity-header">
                        <span class="activity-user">${activity.user}</span>
                        <span class="activity-time">${activity.time}</span>
                    </div>
                    <div class="activity-message">${activity.message}</div>
                    <a href="${activity.url}" class="activity-link">
                        ${piperPrivacyDashboard.i18n.viewDetails} →
                    </a>
                </li>
            `;
        },

        /**
         * Generate action HTML
         */
        getActionHTML: function(action) {
            return `
                <li class="action-item priority-${action.priority}">
                    <div class="action-header">
                        <span class="action-type">${action.type}</span>
                        <span class="due-date">${action.due_date}</span>
                    </div>
                    <div class="action-title">${action.title}</div>
                    <a href="${action.url}" class="button button-small">
                        ${piperPrivacyDashboard.i18n.takeAction}
                    </a>
                </li>
            `;
        },

        /**
         * Handle action button clicks
         */
        handleActionClick: function(url) {
            window.location.href = url;
        },

        /**
         * Animate number changes
         */
        animateNumber: function($element, newValue) {
            const currentValue = parseInt($element.text(), 10);
            if (currentValue === newValue) return;

            $({ value: currentValue }).animate({ value: newValue }, {
                duration: 500,
                step: function() {
                    $element.text(Math.floor(this.value));
                },
                complete: function() {
                    $element.text(newValue);
                }
            });
        },

        /**
         * Show update notice
         */
        showUpdateNotice: function() {
            const $notice = $('<div class="notice notice-success is-dismissible"><p>' + 
                            piperPrivacyDashboard.i18n.dataUpdated + '</p></div>')
                            .hide()
                            .insertAfter('.wrap > h1')
                            .slideDown();

            setTimeout(() => {
                $notice.slideUp(() => $notice.remove());
            }, 3000);
        },

        /**
         * Handle errors
         */
        handleError: function(error) {
            console.error('Dashboard Error:', error);
            const $notice = $('<div class="notice notice-error is-dismissible"><p>' + 
                            piperPrivacyDashboard.i18n.errorLoading + '</p></div>')
                            .hide()
                            .insertAfter('.wrap > h1')
                            .slideDown();

            setTimeout(() => {
                $notice.slideUp(() => $notice.remove());
            }, 5000);
        }
    };

    // Initialize on document ready
    $(document).ready(() => {
        PiperPrivacyDashboard.init();
    });

})(jQuery);