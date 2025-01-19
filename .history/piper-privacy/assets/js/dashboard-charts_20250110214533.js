/**
 * Dashboard Charts JavaScript
 */
(function($) {
    'use strict';

    const PiperPrivacyCharts = {
        charts: {},

        /**
         * Initialize charts
         */
        init: function() {
            this.setupChartDefaults();
            this.createCharts();
            this.setupRefreshTimer();
            this.bindEvents();
        },

        /**
         * Setup Chart.js defaults
         */
        setupChartDefaults: function() {
            Chart.defaults.font.family = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
            Chart.defaults.responsive = true;
            Chart.defaults.maintainAspectRatio = false;
        },

        /**
         * Create dashboard charts
         */
        createCharts: function() {
            this.createTrendChart();
            this.createComplianceChart();
            this.createDistributionCharts();
        },

        /**
         * Create collection trends chart
         */
        createTrendChart: function() {
            const ctx = document.getElementById('collection-trends-chart');
            if (!ctx) return;

            this.charts.trends = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: []
                },
                options: {
                    plugins: {
                        title: {
                            display: true,
                            text: 'Collection Trends (12 Months)'
                        },
                        legend: {
                            position: 'bottom'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });

            this.loadTrendData();
        },

        /**
         * Create compliance rate chart
         */
        createComplianceChart: function() {
            const ctx = document.getElementById('compliance-rate-chart');
            if (!ctx) return;

            this.charts.compliance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Compliance Rate',
                        borderColor: piperPrivacyCharts.colors.primary,
                        backgroundColor: piperPrivacyCharts.colors.primary + '20',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    plugins: {
                        title: {
                            display: true,
                            text: 'Compliance Rate Trend'
                        }
                    },
                    scales: {
                        y: {
                            min: 0,
                            max: 100,
                            ticks: {
                                callback: value => value + '%'
                            }
                        }
                    }
                }
            });
        },

        /**
         * Create distribution charts
         */
        createDistributionCharts: function() {
            // PTA Distribution
            const ptaCtx = document.getElementById('pta-distribution-chart');
            if (ptaCtx) {
                this.charts.ptaDistribution = new Chart(ptaCtx, {
                    type: 'doughnut',
                    data: {
                        labels: [],
                        datasets: [{
                            data: [],
                            backgroundColor: [
                                piperPrivacyCharts.colors.primary,
                                piperPrivacyCharts.colors.warning,
                                piperPrivacyCharts.colors.success,
                                piperPrivacyCharts.colors.danger
                            ]
                        }]
                    },
                    options: {
                        plugins: {
                            title: {
                                display: true,
                                text: 'PTA Status Distribution'
                            }
                        }
                    }
                });
            }

            // PIA Distribution
            const piaCtx = document.getElementById('pia-distribution-chart');
            if (piaCtx) {
                this.charts.piaDistribution = new Chart(piaCtx, {
                    type: 'doughnut',
                    data: {
                        labels: [],
                        datasets: [{
                            data: [],
                            backgroundColor: [
                                piperPrivacyCharts.colors.primary,
                                piperPrivacyCharts.colors.warning,
                                piperPrivacyCharts.colors.success,
                                piperPrivacyCharts.colors.danger
                            ]
                        }]
                    },
                    options: {
                        plugins: {
                            title: {
                                display: true,
                                text: 'PIA Status Distribution'
                            }
                        }
                    }
                });
            }

            this.loadDistributionData();
        },

        /**
         * Setup refresh timer
         */
        setupRefreshTimer: function() {
            setInterval(() => {
                this.refreshChartData();
            }, 300000); // Refresh every 5 minutes
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            $('.refresh-charts').on('click', (e) => {
                e.preventDefault();
                this.refreshChartData();
            });
        },

        /**
         * Load trend data
         */
        loadTrendData: function() {
            $.ajax({
                url: piperPrivacyCharts.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'get_privacy_trend_data',
                    nonce: piperPrivacyCharts.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.updateTrendChart(response.data.collections);
                        this.updateComplianceChart(response.data.compliance);
                    } else {
                        this.handleError(response.data.message);
                    }
                }
            });
        },

        /**
         * Load distribution data
         */
        loadDistributionData: function() {
            $.ajax({
                url: piperPrivacyCharts.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'get_compliance_distribution',
                    nonce: piperPrivacyCharts.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.updateDistributionCharts(response.data);
                    } else {
                        this.handleError(response.data.message);
                    }
                }
            });
        },

        /**
         * Update trend chart
         */
        updateTrendChart: function(data) {
            if (!this.charts.trends) return;

            this.charts.trends.data.labels = data.labels;
            this.charts.trends.data.datasets = data.datasets.map((dataset, index) => ({
                ...dataset,
                borderColor: Object.values(piperPrivacyCharts.colors)[index],
                backgroundColor: Object.values(piperPrivacyCharts.colors)[index] + '20',
                tension: 0.4
            }));

            this.charts.trends.update();
        },

        /**
         * Update compliance chart
         */
        updateComplianceChart: function(data) {
            if (!this.charts.compliance) return;

            this.charts.compliance.data.labels = data.labels;
            this.charts.compliance.data.datasets[0].data = data.datasets[0].data;
            this.charts.compliance.update();
        },

        /**
         * Update distribution charts
         */
        updateDistributionCharts: function(data) {
            // Update PTA Distribution
            if (this.charts.ptaDistribution) {
                this.charts.ptaDistribution.data.labels = data.pta.labels;
                this.charts.ptaDistribution.data.datasets[0].data = data.pta.data;
                this.charts.ptaDistribution.update();
            }

            // Update PIA Distribution
            if (this.charts.piaDistribution) {
                this.charts.piaDistribution.data.labels = data.pia.labels;
                this.charts.piaDistribution.data.datasets[0].data = data.pia.data;
                this.charts.piaDistribution.update();
            }
        },

        /**
         * Refresh all chart data
         */
        refreshChartData: function() {
            $('.dashboard-charts').addClass('loading');
            
            Promise.all([
                this.loadTrendData(),
                this.loadDistributionData()
            ]).then(() => {
                $('.dashboard-charts').removeClass('loading');
                this.showUpdateNotice();
            }).catch(error => {
                this.handleError(error);
            });
        },

        /**
         * Show update notice
         */
        showUpdateNotice: function() {
            const $notice = $('<div class="notice notice-success is-dismissible"><p>' + 
                            'Charts updated successfully</p></div>')
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
            console.error('Chart Error:', error);
            const $notice = $('<div class="notice notice-error is-dismissible"><p>' + 
                            'Error updating charts</p></div>')
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
        PiperPrivacyCharts.init();
    });

})(jQuery);