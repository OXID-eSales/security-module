/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import { PasswordStrength } from "./module/password-validator.js";
import { PasswordGenerator } from "./module/password-generator.js";

document.getElementById("generate_password").addEventListener("click", function() {
    // Generate a new password
    new PasswordGenerator({
        passwordField: document.getElementById("userPassword"),
        confirmPwdField: document.getElementById("userPasswordConfirm"),
        popover: document.getElementById('password-requirements')
    });
});

document.querySelectorAll("div[data-type='passwordStrength']").forEach((el) => {
    new PasswordStrength({
        url: el.getAttribute('data-url'),
        fieldTarget: document.getElementById(el.getAttribute('data-target')),
        progressBar: el.querySelector(".progress-bar"),
        popover: document.getElementById('password-requirements'),
    })
});
