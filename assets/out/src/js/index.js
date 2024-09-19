import { PasswordStrength } from "./module/password-validator.js";

document.querySelectorAll("div[data-type='passwordStrength']").forEach((el) => {
    new PasswordStrength({
        url: el.getAttribute('data-url'),
        fieldTarget: document.getElementById(el.getAttribute('data-target')),
        progressBar: el.querySelector(".progress-bar")
    })
});
