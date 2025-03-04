/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

export class CaptchaRefresh {
    constructor(options) {
        this.url = options.url;
        this.fieldTarget = options.fieldTarget;
        this.delayInMilliseconds = 1000;

        this.registerEvents();
    }

    registerEvents() {
        let self = this;
        let requestDelayTimeout;

        this.fieldTarget.addEventListener('click', function() {
            if (self.signal) {
                self.controller.abort();
            }

            if (typeof requestDelayTimeout === 'undefined' || requestDelayTimeout < Date.now()) {
                self.ajaxRequest();

                requestDelayTimeout = Date.now() + self.delayInMilliseconds;
            }
        });
    }

    ajaxRequest() {
        let self = this;
        self.controller = new AbortController();
        self.signal = self.controller.signal;

        fetch(this.url, {
            method: "GET",
            signal: self.signal,
        }).then(function(response) {
            return response.text();
        }).then(function(data) {
            self.handleResponse(data);
        }).catch(err => {
        });
    }

    handleResponse(captchaResponse) {
        document.querySelectorAll('img.captcha-img').forEach((captchaEl) => {
            captchaEl.src = captchaResponse;
        })
    }
}
