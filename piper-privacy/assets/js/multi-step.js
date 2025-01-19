/* global jQuery */
(function($) {
    'use strict';

    class MultiStepForm {
        constructor($form) {
            this.$form = $form;
            this.$steps = $form.find('.piper-privacy-step');
            this.$progressSteps = $form.find('.piper-privacy-progress-step');
            this.$prevButton = $form.find('#prev-step');
            this.$nextButton = $form.find('#next-step');
            this.$submitButton = $form.find('.rwmb-submit-button');
            this.currentStep = 1;
            this.totalSteps = this.$steps.length;

            this.init();
        }

        init() {
            this.bindEvents();
            this.updateButtons();
            this.updateProgress();
        }

        bindEvents() {
            this.$prevButton.on('click', () => this.prevStep());
            this.$nextButton.on('click', () => this.nextStep());
            this.$form.on('submit', (e) => this.validateStep(e));
        }

        validateStep(e) {
            const $currentStep = this.$steps.filter(`[data-step="${this.currentStep}"]`);
            const $requiredFields = $currentStep.find('[required]');
            let isValid = true;

            $requiredFields.each(function() {
                const $field = $(this);
                if (!$field.val()) {
                    isValid = false;
                    const $wrapper = $field.closest('.rwmb-field');
                    if (!$wrapper.find('.rwmb-error').length) {
                        $wrapper.append('<div class="rwmb-error">This field is required.</div>');
                    }
                    $field.addClass('rwmb-invalid');
                }
            });

            if (!isValid) {
                e.preventDefault();
                return false;
            }

            return true;
        }

        prevStep() {
            if (this.currentStep > 1) {
                this.showStep(this.currentStep - 1);
            }
        }

        nextStep() {
            if (this.validateStep({ preventDefault: () => {} })) {
                if (this.currentStep < this.totalSteps) {
                    this.showStep(this.currentStep + 1);
                }
            }
        }

        showStep(step) {
            this.$steps.hide();
            this.$steps.filter(`[data-step="${step}"]`).show();
            this.currentStep = step;
            this.updateButtons();
            this.updateProgress();
            
            // Scroll to top of form
            $('html, body').animate({
                scrollTop: this.$form.offset().top - 50
            }, 500);
        }

        updateButtons() {
            // Show/hide Previous button
            if (this.currentStep === 1) {
                this.$prevButton.hide();
            } else {
                this.$prevButton.show();
            }

            // Show/hide Next and Submit buttons
            if (this.currentStep === this.totalSteps) {
                this.$nextButton.hide();
                this.$submitButton.show();
            } else {
                this.$nextButton.show();
                this.$submitButton.hide();
            }
        }

        updateProgress() {
            this.$progressSteps.removeClass('active completed');
            
            // Mark current step as active
            this.$progressSteps.eq(this.currentStep - 1).addClass('active');
            
            // Mark previous steps as completed
            for (let i = 0; i < this.currentStep - 1; i++) {
                this.$progressSteps.eq(i).addClass('completed');
            }
        }
    }

    // Initialize multi-step forms
    $(document).ready(function() {
        $('.rwmb-form').each(function() {
            new MultiStepForm($(this));
        });
    });

})(jQuery);
