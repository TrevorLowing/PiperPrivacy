/**
 * Dashboard Charts JavaScript
 * Handles chart initialization and data visualization for privacy analytics
 */

(function($) {
    'use strict';

    // Chart color palette
    const chartColors = {
        blue: '#007bff',
        green: '#28a745',
        red: '#dc3545',
        yellow: '#ffc107',
        gray: '#6c757d',
        lightBlue: '#17a2b8',
        purple: '#6f42c1',
        orange: '#fd7e14'
    };

    // Chart configuration defaults
    const defaultChartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        legend: {
            position: 'bottom',
            labels: {
                padding: 20
            }
        },
        tooltips: {
            mode: 'index',
            intersect: false
        }
    };

    class DashboardCharts {
        constructor() {
            this.charts = {};
            this.initializeCharts();
            this.setupEventListeners();
        }

        /**
         * Initialize dashboard charts
         */
        initializeCharts() {
            this.fetchAnalyticsData()
                .then(data => {
                    this.createCollectionsChart(data.collections);
                    this.createImpactsChart(data.impacts);
                    this.createThresholdsChart(data.thresholds);
                    this.createWorkflowMetricsChart(data.workflow);
                    this.updateStatistics(data);
                })
                .catch(error => {
                    console.error('Error initializing charts:', error);
                    this.showError('Failed to load analytics data');
                });
        }

        /**
         * Fetch analytics data from the server
         */
        fetchAnalyticsData() {
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'get_privacy_stats',
                        nonce: piperPrivacy.nonce
                    },
                    success: response => {
                        if (response.success) {
                            resolve(response.data);
                        } else {
                            reject(response.data);
                        }
                    },
                    error: (xhr, status, error) => {
                        reject(error);
                    }
                });
            });
        }

        /**
         * Create collections status chart
         */
        createCollectionsChart(data) {
            const ctx = document.getElementById('collectionsChart').getContext('2d');
            
            this.charts.collections = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(data.by_status),
                    datasets: [{
                        data: Object.values(data.by_status),
                        backgroundColor: Object.values(chartColors)
                    }]
                },
                options: {
                    ...defaultChartOptions,
                    title: {
                        display: true,
                        text: 'Privacy Collections by Status'
                    }
                }
            });
        }

        /**
         * Create impact assessments chart
         */
        createImpactsChart(data) {
            const ctx = document.getElementById('impactsChart').getContext('2d');
            
            this.charts.impacts = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: Object.keys(data.by_risk_level),
                    datasets: [{
                        label: 'Risk Level Distribution',
                        data: Object.values(data.by_risk_level),
                        backgroundColor: chartColors.red
                    }]
                },
                options: {
                    ...defaultChartOptions,
                    title: {
                        display: true,
                        text: 'Privacy Impact Assessments by Risk Level'
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true
                            }
                        }]
                    }
                }
            });
        }

        /**
         * Create threshold assessments chart
         */
        createThresholdsChart(data) {
            const ctx = document.getElementById('thresholdsChart').getContext('2d');
            
            this.charts.thresholds = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: Object.keys(data.by_outcome),
                    datasets: [{
                        data: Object.values(data.by_outcome),
                        backgroundColor: [
                            chartColors.green,
                            chartColors.yellow,
                            chartColors.red
                        ]
                    }]
                },
                options: {
                    ...defaultChartOptions,
                    title: {
                        display: true,
                        text: 'Threshold Assessment Outcomes'
                    }
                }
            });
        }

        /**
         * Create workflow metrics chart
         */
        createWorkflowMetricsChart(data) {
            const ctx = document.getElementById('workflowChart').getContext('2d');
            
            // Convert bottlenecks data for chart
            const bottlenecks = data.bottlenecks.reduce((acc, item) => {
                acc.stages.push(item.stage);
                acc.durations.push(item.avg_duration);
                return acc;
            }, { stages: [], durations: [] });

            this.charts.workflow = new Chart(ctx, {
                type: 'horizontalBar',
                data: {
                    labels: bottlenecks.stages,
                    datasets: [{
                        label: 'Average Duration (days)',
                        data: bottlenecks.durations,
                        backgroundColor: chartColors.blue
                    }]
                },
                options: {
                    ...defaultChartOptions,
                    title: {
                        display: true,
                        text: 'Workflow Stage Duration Analysis'
                    },
                    scales: {
                        xAxes: [{
                            ticks: {
                                beginAtZero: true
                            }
                        }]
                    }
                }
            });
        }

        /**
         * Update statistics display
         */
        updateStatistics(data) {
            $('#totalCollections').text(data.collections.total);
            $('#totalImpacts').text(data.impacts.total);
            $('#totalThresholds').text(data.thresholds.total);
            $('#averageCompletionTime').text(data.workflow.average_completion_time.toFixed(1));
            $('#overdueTasks').text(data.workflow.overdue_tasks);
            $('#completionRate').text(data.workflow.completion_rate.toFixed(1) + '%');
        }

        /**
         * Setup event listeners
         */
        setupEventListeners() {
            $('#exportReport').on('click', this.handleExport.bind(this));
            $('#refreshAnalytics').on('click', this.handleRefresh.bind(this));
            
            // Date range filter
            $('#dateRange').on('change', e => {
                const range = $(e.target).val();
                this.updateChartsDateRange(range);
            });
        }

        /**
         * Handle export button click
         */
        handleExport(e) {
            e.preventDefault();
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'export_analytics_report',
                    nonce: piperPrivacy.nonce
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: response => {
                    const url = window.URL.createObjectURL(response);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;
                    a.download = 'privacy-analytics-' + new Date().toISOString().split('T')[0] + '.csv';
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                },
                error: () => {
                    this.showError('Failed to export analytics report');
                }
            });
        }

        /**
         * Handle refresh button click
         */
        handleRefresh() {
            this.initializeCharts();
        }

        /**
         * Update charts based on date range
         */
        updateChartsDateRange(range) {
            this.fetchAnalyticsData(range)
                .then(data => {
                    Object.keys(this.charts).forEach(key => {
                        if (this.charts[key]) {
                            this.charts[key].destroy();
                        }
                    });
                    
                    this.createCollectionsChart(data.collections);
                    this.createImpactsChart(data.impacts);
                    this.createThresholdsChart(data.thresholds);
                    this.createWorkflowMetricsChart(data.workflow);
                    this.updateStatistics(data);
                })
                .catch(error => {
                    console.error('Error updating charts:', error);
                    this.showError('Failed to update analytics data');
                });
        }

        /**
         * Show error message
         */
        showError(message) {
            const errorDiv = $('<div>')
                .addClass('notice notice-error')
                .html(`<p>${message}</p>`);
            
            $('.wrap').prepend(errorDiv);
            
            setTimeout(() => {
                errorDiv.fadeOut(() => errorDiv.remove());
            }, 5000);
        }
    }

    // Initialize dashboard charts when document is ready
    $(document).ready(() => {
        new DashboardCharts();
    });

})(jQuery);
