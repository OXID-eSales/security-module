/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */
export class PasswordGenerator {

    constructor(options) {
        this.passwordField = options.passwordField;
        this.confirmPwdField = options.confirmPwdField;
        this.popover = options.popover;

        this.addStrongPasswordSuggestion();
    }

    addStrongPasswordSuggestion() {
        let password = this.generatePassword();

        this.passwordField.value = password;
        this.confirmPwdField.value = password;

        this.triggerRightArrowKeyup();

        this.popover.classList.add('d-none');
    }

    generatePassword() {
        const uppercase = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        const lowercase = "abcdefghijklmnopqrstuvwxyz";
        const numbers = "0123456789";
        const specialChars = "!@#$%^*()_+=-[]{}|;:,.<>?&";

        let password = "";

        // Ensure at least one character from each required set is included
        password += uppercase.charAt(Math.floor(Math.random() * uppercase.length));
        password += lowercase.charAt(Math.floor(Math.random() * lowercase.length));
        password += numbers.charAt(Math.floor(Math.random() * numbers.length));
        password += specialChars.charAt(Math.floor(Math.random() * specialChars.length));

        // Fill the rest of the password with random characters until it reaches 20 characters or more
        const allChars = uppercase + lowercase + numbers + specialChars;
        for (let i = password.length; i < 20; i++) {
            password += allChars.charAt(Math.floor(Math.random() * allChars.length));
        }

        // Shuffle the password to ensure randomness
        password = password.split('').sort(() => 0.5 - Math.random()).join('');

        return password;
    }

    triggerRightArrowKeyup() {
        const rightArrowEvent = new KeyboardEvent('keyup', {
            key: 'ArrowRight',
            keyCode: 39,
            code: 'ArrowRight',
            bubbles: true
        });

        // Dispatch the event to trigger the keyup listener
        this.passwordField.dispatchEvent(rightArrowEvent);
    }
}
