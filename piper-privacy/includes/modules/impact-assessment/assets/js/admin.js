/* global jQuery, wp, ppAssessment */
(function($) {
    'use strict';

    const PiperPrivacyAssessment = {
        init: function() {
            this.container = $('.pp-assessment-container');
            this.bindEvents();
            this.loadAssessments();
        },

        bindEvents: function() {
            // New assessment
            $('#pp-new-assessment').on('click', (e) => {
                e.preventDefault();
                this.showForm();
            });

            // Form submission
            this.container.on('submit', '#pp-assessment-form', (e) => {
                e.preventDefault();
                this.saveAssessment($(e.currentTarget));
            });

            // Cancel form
            this.container.on('click', '.pp-cancel-form', () => {
                this.showList();
            });

            // View assessment
            this.container.on('click', '.pp-view-assessment', (e) => {
                e.preventDefault();
                const id = $(e.currentTarget).data('id');
                this.viewAssessment(id);
            });

            // Edit assessment
            this.container.on('click', '.pp-edit-assessment', (e) => {
                e.preventDefault();
                const id = $(e.currentTarget).data('id');
                this.editAssessment(id);
            });

            // Delete assessment
            this.container.on('click', '.pp-delete-assessment', (e) => {
                e.preventDefault();
                const id = $(e.currentTarget).data('id');
                this.deleteAssessment(id);
            });

            // Back to list
            this.container.on('click', '.pp-back-to-list', () => {
                this.showList();
            });
        },

        loadAssessments: function() {
            wp.apiRequest({
                path: ppAssessment.apiRoot + '/assessments',
                method: 'GET'
            }).then((response) => {
                this.container.find('#pp-assessment-list').html(response);
            });
        },

        showForm: function(data = {}) {
            this.container.find('#pp-assessment-form').show();
            this.container.find('#pp-assessment-list, #pp-assessment-single').hide();

            // Reset form
            const form = this.container.find('#pp-assessment-form form');
            form[0].reset();
            form.find('[name="id"]').val(data.id || '');
            
            // Fill form data
            if (data.id) {
                Object.keys(data).forEach(key => {
                    form.find(`[name="${key}"]`).val(data[key]);
                });
            }
        },

        showList: function() {
            this.loadAssessments();
            this.container.find('#pp-assessment-list').show();
            this.container.find('#pp-assessment-form, #pp-assessment-single').hide();
        },

        saveAssessment: function(form) {
            const data = {};
            form.serializeArray().forEach(item => {
                data[item.name] = item.value;
            });

            const method = data.id ? 'PUT' : 'POST';
            const path = data.id 
                ? ppAssessment.apiRoot + '/assessments/' + data.id
                : ppAssessment.apiRoot + '/assessments';

            wp.apiRequest({
                path: path,
                method: method,
                data: data
            }).then(() => {
                this.showList();
            });
        },

        viewAssessment: function(id) {
            wp.apiRequest({
                path: ppAssessment.apiRoot + '/assessments/' + id,
                method: 'GET'
            }).then((response) => {
                this.container.find('#pp-assessment-single').html(response).show();
                this.container.find('#pp-assessment-list, #pp-assessment-form').hide();
            });
        },

        editAssessment: function(id) {
            wp.apiRequest({
                path: ppAssessment.apiRoot + '/assessments/' + id,
                method: 'GET'
            }).then((response) => {
                this.showForm(response);
            });
        },

        deleteAssessment: function(id) {
            if (!confirm(wp.i18n.__('Are you sure you want to delete this assessment?', 'piper-privacy'))) {
                return;
            }

            wp.apiRequest({
                path: ppAssessment.apiRoot + '/assessments/' + id,
                method: 'DELETE'
            }).then(() => {
                this.showList();
            });
        }
    };

    $(document).ready(() => {
        PiperPrivacyAssessment.init();
    });
})(jQuery);
