/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */
export class CaptchaValidator {
    constructor(options) {
        this.form = options.form;
        this.fieldTarget = options.fieldTarget;

        this.registerEvents();
    }

    registerEvents() {
        let self = this;

        this.form.addEventListener("submit", function (event) {

            if (self.fieldTarget && !self.fieldTarget.value.trim()) {
                event.preventDefault();
                self.fieldTarget.focus();
                self.fieldTarget.setAttribute('required', 'true');
                self.fieldTarget.classList.add('is-invalid');
            }
        });
    }
}
