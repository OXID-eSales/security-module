/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */
let delayInMilliseconds = 1000;
let requestDelayTimeoutId;

const passwords = document.querySelectorAll("input[id='userPassword'], input[id='passwordNew'], input[id='forgotPassword']");
passwords.forEach(function (el) {
    el.addEventListener("keyup", function (e) {
        let password = this.value;
        if (requestDelayTimeoutId !== undefined) {
            clearTimeout(requestDelayTimeoutId);
        }

        requestDelayTimeoutId = setTimeout(function () {
            ajaxRequest(password);
        }, delayInMilliseconds);
    });
});

function ajaxRequest(password) {
    const xhr = new XMLHttpRequest();

    xhr.onreadystatechange = function() {
        if (this.readyState === 4 && this.status === 200) {
            let jsonResponse = JSON.parse(xhr.responseText);
            let progressBar = document.querySelector('.progress-bar');

            progressBar.classList.remove('bg-danger', 'bg-warning', 'bg-success');

            switch (jsonResponse.strength) {
                case 0:
                    progressBar.style.width = "15%";
                    progressBar.parentNode.setAttribute('aria-valuenow', '15');
                    progressBar.classList.add('bg-danger')
                    break
                case 1:
                    progressBar.style.width = "35%";
                    progressBar.parentNode.setAttribute('aria-valuenow', '35');
                    progressBar.classList.add('bg-danger')
                    break
                case 2:
                    progressBar.style.width = "50%";
                    progressBar.parentNode.setAttribute('aria-valuenow', '50');
                    progressBar.classList.add('bg-warning')
                    break
                case 3:
                    progressBar.style.width = "75%";
                    progressBar.parentNode.setAttribute('aria-valuenow', '75');
                    progressBar.classList.add('bg-success')
                    break
                case 4:
                    progressBar.style.width = "100%";
                    progressBar.parentNode.setAttribute('aria-valuenow', '100');
                    progressBar.classList.add('bg-success')
                    break
                default:
                    break
            }
        }
    };

    xhr.open("POST", "http://localhost.local/?cl=password&fnc=passwordStrength", false);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send('password=' + password);
}
