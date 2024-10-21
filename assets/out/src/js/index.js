/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import { PasswordStrength } from "./module/password-validator.js";
import { PasswordGenerator } from "./module/password-generator.js";

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
