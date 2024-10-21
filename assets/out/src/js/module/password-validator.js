/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */
export class PasswordStrength {
    constructor(options) {
        this.url = options.url;
        this.fieldTarget = options.fieldTarget;
        this.progressBar = options.progressBar;
        this.popover = options.popover;
        this.delayInMilliseconds = 500;

        this.registerEvents();
    }

    registerEvents() {
        let self = this;
        let requestDelayTimeoutId;

        this.fieldTarget.addEventListener("keyup", function (e) {
            let password = this.value;

            if (self.signal) {
                self.controller.abort();
            }

            self.progressBar.classList.add('loading');

            if (requestDelayTimeoutId !== undefined) {
                clearTimeout(requestDelayTimeoutId);
            }

            requestDelayTimeoutId = setTimeout(function () {
                self.ajaxRequest(password);
            }, self.delayInMilliseconds);
        });

        this.fieldTarget.addEventListener('focus', function() {
            self.popover.classList.remove('d-none');
        });

        this.fieldTarget.addEventListener('focusout', function(e) {
            if (!self.popover.contains(e.relatedTarget)) {
                self.popover.classList.add('d-none');
            }
        });
    }

    ajaxRequest(password) {
        let self = this;
        self.controller = new AbortController();
        self.signal = self.controller.signal;

        if (password.length < 1) {
            self.setProgressBar(0, '', '');
            return;
        }

        self.request = fetch(self.url, {
            method: "POST",
            signal: self.signal,
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: 'password=' + encodeURIComponent(password),
        }).then(function(response) {
            return response.json();
        })
        .then(function(data) {
            self.handleResponse(data);
        }).catch(err => {
        });
    }

    handleResponse(jsonResponse){
        let self = this;

        switch (jsonResponse.strength) {
            case 0:
                self.setProgressBar(15, 'very-weak', jsonResponse.message);
                break
            case 1:
                self.setProgressBar(35, 'weak',  jsonResponse.message);
                break
            case 2:
                self.setProgressBar(50, 'medium', jsonResponse.message);
                break
            case 3:
                self.setProgressBar(75, 'strong', jsonResponse.message);
                break
            case 4:
                self.setProgressBar(100, 'very-strong', jsonResponse.message);
                break
            default:
                break
        }

        this.progressBar.classList.remove('loading');
    }

    setProgressBar(value, css, text) {
        let progressBar = this.progressBar;
        progressBar.classList.remove('very-weak', 'very-strong', 'weak', 'strong', 'medium');

        progressBar.parentNode.setAttribute('aria-valuenow', value);

        if (css) {
            progressBar.classList.add(css);
        }
        progressBar.innerText = text;
    }
}
