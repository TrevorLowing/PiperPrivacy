/**
 * Chart Filters JavaScript
 */
(function($) {
    'use strict';

    const ChartFilters = {
        /**
         * Initialize filters
         */
        init: function() {
            this.setupFilterControls();
            this.bindEvents();
            this.initializeDateRanges();
        },

        /**
         * Setup filter controls HTML
         */
        setupFilterControls: function() {
            const filterHtml = `
                <div class="chart-filters">
                    <div class="filter-group date-range">
                        <label for="date-range">${piperPrivacyCharts.i18n.dateRange}</label>
                        <select id="date-range" class="filter-select">
                            <option value="30">30 ${piperPrivacyCharts.i18n.days}</option>
                            <option value="90">90 ${piperPrivacyCharts.i18n.days}</option>
                            <option value="180">180 ${piperPrivacyCharts.i18n.days}</option>
                            <option value="365" selected>365 ${piperPrivacyCharts.i18n.days}</option>
                            <option value="custom">${piperPrivacyCharts.i18n.custom}</option>
                        </select>
                        <div class="custom-date-range" style="display: none;">
                            <input type="date" id="date-start" class="filter-date">
                            <input type="date" id="date-end" class="filter-date">
                        </div>
                    </div>

                    <div class="filter-group status-filter">
                        <label>${piperPrivacyCharts.i18n.status}</label>
                        <div class="checkbox-group">
                            <label>
                                <input type="checkbox" value="draft" checked> 
                                ${piperPrivacyCharts.i18n.draft}
                            </label>
                            <label>
                                <input type="checkbox" value="active" checked> 
                                ${piperPrivacyCharts.i18n.active}
                            </label>
                            <label>
                                <input type="checkbox" value="review" checked> 
                                ${piperPrivacyCharts.i18n.review}
                            </label>
                            <label>
                                <input type="checkbox" value="completed" checked> 
                                ${piperPrivacyCharts.i18n.completed}
                            </label>
                        </div>
                    </div>

                    <div class="filter-group type-filter">
                        <label>${piperPrivacyCharts.i18n.type}</label>
                        <div class="checkbox-group">
                            <label>
                                <input type="checkbox" value="collections" checked> 
                                ${piperPrivacyCharts.i18n.collections}
                            </label>
                            <label>
                                <input type="checkbox" value="pta" checked> 
                                ${piperPrivacyCharts.i18n.pta}
                            </label>
                            <label>
                                <input type="checkbox" value="pia" checked> 
                                ${piperPrivacyCharts.i18n.pia}
                            </label>
                        </div>
                    </div>

                    <div class="filter-actions">
                        <button type="button" class="button apply-filters">
                            ${piperPrivacyCharts.i18n.apply}
                        </button>
                        <button type="button" class="button reset-filters">
                            ${piperPrivacyCharts.i18n.reset}
                        </button>
                    </div>
                </div>`;

            $('.dashboard-charts').prepend(filterHtml);
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // Date range selection
            $('#date-range').on('change', (e) => {
                const $customRange = $('.custom-date-range');
                if (e.target.value === 'custom') {
                    $customRange.slideDown();
                } else {
                    $customRange.slideUp();
                }
            });

            // Filter application
            $('.apply-filters').on('click', () => {
                this.applyFilters();
            });

            // Filter reset
            $('.reset-filters').on('click', () => {
                this.resetFilters();
            });

            // Status filter changes
            $('.status-filter input').on('change', () => {
                this.updateVisibleDatasets();
            });

            // Type filter changes
            $('.type-filter input').on('change', () => {
                this.updateVisibleCharts();
            });
        },

        /**
         * Initialize date ranges
         */
        initializeDateRanges: function() {
            const today = new Date();
            const startDate = new Date();
            startDate.setDate(today.getDate() - 365); // Default to 1 year

            $('#date-start').val(this.formatDate(startDate));
            $('#date-end').val(this.formatDate(today));
        },

        /**
         * Format date for input
         */
        formatDate: function(date) {
            return date.toISOString().split('T')[0];
        },

        /**
         * Apply selected filters
         */
        applyFilters: function() {
            const filters = this.getActiveFilters();
            
            $('.dashboard-charts').addClass('loading');

            $.ajax({
                url: piperPrivacyCharts.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'get_filtered_chart_data',
                    nonce: piperPrivacyCharts.nonce,
                    filters: filters
                },
                success: (response) => {
                    if (response.success) {
                        PiperPrivacyCharts.updateAllCharts(response.data);
                        $('.dashboard-charts').removeClass('loading');
                        this.showFilterNotice('success');
                    } else {
                        this.handleFilterError(response.data.message);
                    }
                },
                error: (error) => {
                    this.handleFilterError(error);
                }
            });
        },

        /**
         * Get active filters
         */
        getActiveFilters: function() {
            const dateRange = $('#date-range').val();
            
            return {
                date_range: dateRange,
                date_start: dateRange === 'custom' ? $('#date-start').val() : null,
                date_end: dateRange === 'custom' ? $('#date-end').val() : null,
                statuses: $('.status-filter input:checked').map(function() {
                    return $(this).val();
                }).get(),
                types: $('.type-filter input:checked').map(function() {
                    return $(this).val();
                }).get()
            };
        },

        /**
         * Reset filters to default
         */
        resetFilters: function() {
            $('#date-range').val('365');
            $('.custom-date-range').slideUp();
            $('.status-filter input, .type-filter input').prop('checked', true);
            
            this.initializeDateRanges();
            this.applyFilters();
        },

        /**
         * Update visible datasets in charts
         */
        updateVisibleDatasets: function() {
            const activeStatuses = $('.status-filter input:checked').map(function() {
                return $(this).val();
            }).get();

            PiperPrivacyCharts.charts.trends.data.datasets.forEach(dataset => {
                dataset.hidden = !activeStatuses.includes(dataset.label.toLowerCase());
            });

            PiperPrivacyCharts.charts.trends.update();
        },

        /**
         * Update visible charts based on type filter
         */
        updateVisibleCharts: function() {
            const activeTypes = $('.type-filter input:checked').map(function() {
                return $(this).val();
            }).get();

            $('.chart-container').each(function() {
                const $container = $(this);
                const chartType = $container.data('chart-type');
                
                if (activeTypes.includes(chartType)) {
                    $container.slideDown();
                } else {
                    $container.slideUp();
                }
            });
        },

        /**
         * Show filter notification
         */
        showFilterNotice: function(type) {
            const message = type === 'success' 
                ? piperPrivacyCharts.i18n.filtersApplied 
                : piperPrivacyCharts.i18n.filterError;

            const $notice = $(`<div class="notice notice-${type} is-dismissible"><p>${message}</p></div>`)
                .hide()
                .insertAfter('.chart-filters')
                .slideDown();

            setTimeout(() => {
                $notice.slideUp(() => $notice.remove());
            }, 3000);
        },

        /**
         * Handle filter errors
         */
        handleFilterError: function(error) {
            console.error('Filter Error:', error);
            $('.dashboard-charts').removeClass('loading');
            this.showFilterNotice('error');
        }
    };

    // Initialize on document ready
    $(document).ready(() => {
        ChartFilters.init();
    });

})(jQuery);