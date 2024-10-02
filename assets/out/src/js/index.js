/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

import { PasswordStrength } from "./module/password-validator.js";

document.querySelectorAll("div[data-type='passwordStrength']").forEach((el) => {
    new PasswordStrength({
        url: el.getAttribute('data-url'),
        fieldTarget: document.getElementById(el.getAttribute('data-target')),
        progressBar: el.querySelector(".progress-bar"),
        popover: document.getElementById('password-requirements'),
    })
});
