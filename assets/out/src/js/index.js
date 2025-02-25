/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import { PasswordStrength } from "./module/password-validator.js";
import { PasswordGenerator } from "./module/password-generator.js";
import { CaptchaRefresh } from "./module/captcha-refresh.js";
import { CaptchaAudio } from "./module/captcha-audio.js";

document.querySelectorAll("div[data-type='passwordStrength']").forEach((el) => {
    new PasswordStrength({
        url: el.getAttribute('data-url'),
        fieldTarget: document.getElementById(el.getAttribute('data-target')),
        progressBar: el.querySelector(".progress-bar"),
        popover: document.getElementById('password-requirements'),
    })

    document.getElementById("generate_password").addEventListener("click", function(e) {
        // Generate a new password
        new PasswordGenerator({
            passwordField: document.getElementById(el.getAttribute('data-target')),
            confirmPwdField: document.getElementById(el.getAttribute('data-target-confirmation')),
            popover: document.getElementById('password-requirements')
        });
    });
});

document.querySelectorAll('.password-toggle').forEach((el) => {
    el.addEventListener('click', function() {
        let passwordField = document.getElementById(this.getAttribute('data-target'));
        passwordField.type = (passwordField.type === "password" ? "text" : "password")
    });
});

document.querySelectorAll('.captcha-reload').forEach((el) => {
    el.addEventListener('click', function() {
        new CaptchaRefresh({
            url: document.getElementById('captcha').getAttribute('data-url')
        })
    });
});

document.querySelectorAll('.captcha-play').forEach((el) => {
    el.addEventListener('click', function() {
        new CaptchaAudio({
            url: document.getElementById('captcha').getAttribute('data-url-audio')
        })
    });
});
