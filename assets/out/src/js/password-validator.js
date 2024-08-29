/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */


//userPassword?
//passwordNew?
//forgotPassword?

const input = document.querySelector("input[id='userPassword']");

input.addEventListener("keyup", ajaxRequest);

function ajaxRequest() {
    var xhr = new XMLHttpRequest();

    xhr.onreadystatechange = function() {
        if (this.readyState === 4 && this.status === 200) {
            var jsonResponse = JSON.parse(xhr.responseText);

            var progressBar = document.querySelector('.progress-bar');
            progressBar.classList.remove('bg-danger', 'bg-warning', 'bg-success');
            document.querySelector('.progress-bar').style.width = (jsonResponse.strength * 25) + "%";
            switch (jsonResponse.strength) {
                case 0:
                    progressBar.classList.add('bg-danger')
                    break
                case 1:
                    progressBar.classList.add('bg-danger')
                    break
                case 2:
                    progressBar.classList.add('bg-warning')
                    break
                case 3:
                    progressBar.classList.add('bg-success')
                    break
                case 4:
                    progressBar.classList.add('bg-success')
                    break
                default:
                    break
            }
        }
    };

    xhr.open("POST", "http://localhost.local/?cl=password&fnc=passwordStrength", true);
    xhr.send();
}
