/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */
export class PasswordStrength {
    constructor(options) {
        this.url = options.url;
        this.fieldTarget = options.fieldTarget;
        this.progressBar = options.progressBar;

        this.registerEvents();
    }

    registerEvents() {
        let self = this;
        let requestDelayTimeoutId;

        this.fieldTarget.addEventListener("keyup", function (e) {
            let password = this.value;
            if (requestDelayTimeoutId !== undefined) {
                clearTimeout(requestDelayTimeoutId);
            }

            requestDelayTimeoutId = setTimeout(function () {
                self.ajaxRequest(password);
            }, self.delayInMilliseconds);
        });
    }

    ajaxRequest(password) {
        let self = this;

        if (password.length < 1) {
            self.setProgressBar(0, '', '');
            return;
        }

        let xhr = new XMLHttpRequest();

        xhr.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                self.handleResponse(xhr.responseText);
            }
        };

        xhr.open("POST", self.url, false);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.send('password=' + password);
    }

    handleResponse(responseText){
        let self = this;
        let jsonResponse = JSON.parse(responseText);

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
