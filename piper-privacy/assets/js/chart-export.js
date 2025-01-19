/**
 * Chart Export Functionality
 */
(function($) {
    'use strict';

    const ChartExport = {
        /**
         * Initialize export functionality
         */
        init: function() {
            this.addExportButtons();
            this.bindEvents();
        },

        /**
         * Add export buttons to charts
         */
        addExportButtons: function() {
            const exportMenuHTML = `
                <div class="chart-export-menu">
                    <button class="button dropdown-toggle" type="button">
                        ${piperPrivacyCharts.i18n.export} 
                        <span class="dashicons dashicons-arrow-down-alt2"></span>
                    </button>
                    <ul class="export-options">
                        <li>
                            <button type="button" data-format="png">
                                <span class="dashicons dashicons-format-image"></span>
                                ${piperPrivacyCharts.i18n.exportImage}
                            </button>
                        </li>
                        <li>
                            <button type="button" data-format="pdf">
                                <span class="dashicons dashicons-pdf"></span>
                                ${piperPrivacyCharts.i18n.exportPDF}
                            </button>
                        </li>
                        <li>
                            <button type="button" data-format="csv">
                                <span class="dashicons dashicons-media-spreadsheet"></span>
                                ${piperPrivacyCharts.i18n.exportCSV}
                            </button>
                        </li>
                    </ul>
                </div>`;

            $('.chart-container').each(function() {
                $(this).prepend(exportMenuHTML);
            });
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // Toggle export menu
            $(document).on('click', '.dropdown-toggle', function(e) {
                e.stopPropagation();
                $(this).siblings('.export-options').toggleClass('active');
            });

            // Handle export option selection
            $(document).on('click', '.export-options button', (e) => {
                e.stopPropagation();
                const $button = $(e.currentTarget);
                const format = $button.data('format');
                const $chartContainer = $button.closest('.chart-container');
                const chartId = $chartContainer.find('canvas').attr('id');
                
                this.exportChart(chartId, format);
                $button.closest('.export-options').removeClass('active');
            });

            // Close menu when clicking outside
            $(document).on('click', (e) => {
                if (!$(e.target).closest('.chart-export-menu').length) {
                    $('.export-options').removeClass('active');
                }
            });
        },

        /**
         * Export chart in specified format
         */
        exportChart: function(chartId, format) {
            const chart = PiperPrivacyCharts.charts[this.getChartKey(chartId)];
            if (!chart) return;

            switch (format) {
                case 'png':
                    this.exportAsImage(chart);
                    break;
                case 'pdf':
                    this.exportAsPDF(chart);
                    break;
                case 'csv':
                    this.exportAsCSV(chart);
                    break;
            }
        },

        /**
         * Get chart key from canvas ID
         */
        getChartKey: function(canvasId) {
            const keyMap = {
                'collection-trends-chart': 'trends',
                'compliance-rate-chart': 'compliance',
                'pta-distribution-chart': 'ptaDistribution',
                'pia-distribution-chart': 'piaDistribution'
            };
            return keyMap[canvasId];
        },

        /**
         * Export chart as image
         */
        exportAsImage: function(chart) {
            const link = document.createElement('a');
            link.download = 'privacy-chart.png';
            link.href = chart.toBase64Image();
            link.click();
        },

        /**
         * Export chart as PDF
         */
        exportAsPDF: function(chart) {
            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF('landscape');
            const canvas = chart.canvas;
            const chartTitle = chart.options.plugins.title.text;

            // Add title
            pdf.setFontSize(16);
            pdf.text(chartTitle, 15, 15);

            // Add chart
            const imgData = canvas.toDataURL('image/png');
            const imgProps = pdf.getImageProperties(imgData);
            const pdfWidth = pdf.internal.pageSize.getWidth() - 30;
            const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

            pdf.addImage(imgData, 'PNG', 15, 30, pdfWidth, pdfHeight);

            // Add metadata
            pdf.setFontSize(10);
            const timestamp = new Date().toLocaleString();
            pdf.text(`Generated: ${timestamp}`, 15, pdf.internal.pageSize.getHeight() - 10);

            pdf.save('privacy-chart.pdf');
        },

        /**
         * Export chart data as CSV
         */
        exportAsCSV: function(chart) {
            const rows = [['Label', ...chart.data.datasets.map(ds => ds.label)]];
            
            chart.data.labels.forEach((label, index) => {
                rows.push([
                    label,
                    ...chart.data.datasets.map(ds => ds.data[index])
                ]);
            });

            const csvContent = rows.map(row => row.join(',')).join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            
            link.href = URL.createObjectURL(blob);
            link.download = 'privacy-chart-data.csv';
            link.click();
        },

        /**
         * Show export notification
         */
        showExportNotice: function(success = true) {
            const message = success 
                ? piperPrivacyCharts.i18n.exportSuccess 
                : piperPrivacyCharts.i18n.exportError;
            
            const $notice = $(`<div class="notice notice-${success ? 'success' : 'error'} is-dismissible">
                <p>${message}</p>
            </div>`).hide().insertAfter('.wrap > h1').slideDown();

            setTimeout(() => {
                $notice.slideUp(() => $notice.remove());
            }, 3000);
        },

        /**
         * Handle export errors
         */
        handleExportError: function(error) {
            console.error('Export Error:', error);
            this.showExportNotice(false);
        }
    };

    // Initialize on document ready
    $(document).ready(() => {
        // Load PDF library
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
        script.onload = () => {
            ChartExport.init();
        };
        document.head.appendChild(script);
    });

})(jQuery);