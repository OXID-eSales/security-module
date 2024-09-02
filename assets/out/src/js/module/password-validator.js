/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */
export class PasswordStrength {
    constructor() {
        this.delayInMilliseconds = 1000;

        this.registerEvents();
    }

    registerEvents() {
        let self = this;
        let requestDelayTimeoutId;

        document.querySelectorAll("div[data-type='passwordStrength']").forEach((el) => {
            let url = el.getAttribute('data-url');
            this.setRequestUrl(url);

            let fieldTarget = el.getAttribute('data-target');
            document.getElementById(fieldTarget).addEventListener("keyup", function (e) {
                let password = this.value;
                if (requestDelayTimeoutId !== undefined) {
                    clearTimeout(requestDelayTimeoutId);
                }

                requestDelayTimeoutId = setTimeout(function () {
                    self.ajaxRequest(password);
                }, self.delayInMilliseconds);
            });
        });
    }

    setRequestUrl(url) {
        this.url = url;
    }

    ajaxRequest(password) {
        let self = this;
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
        let progressBar = document.querySelector('.progress-bar');
        progressBar.classList.remove('very-weak', 'very-strong', 'weak', 'strong', 'medium');

        progressBar.parentNode.setAttribute('aria-valuenow', value);
        progressBar.classList.add(css);
        progressBar.innerText = text;
    }
}
