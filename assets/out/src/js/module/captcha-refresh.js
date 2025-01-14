/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

export class CaptchaRefresh {
    constructor(options) {
        this.url = options.url;

        this.ajaxRequest();
    }

    ajaxRequest() {
        let self = this;

        fetch(this.url, {
            method: "GET"
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
