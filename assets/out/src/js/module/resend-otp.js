/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

export class ResendOtp {
    constructor(button, options = {}) {
        const {
            hintId = 'resend-hint',
            submitButtonId = 'auth_submit',
            attemptsDisplayId = 'remaining-attempts',
            codeInputId = 'auth_code',
            maxAttempts = 5
        } = options;

        this.btn = button;
        this.hint = document.getElementById(hintId);
        this.submitBtn = document.getElementById(submitButtonId);
        this.attemptsDisplay = document.getElementById(attemptsDisplayId);
        this.codeInput = document.getElementById(codeInputId);
        this.maxAttempts = maxAttempts;

        this.cooldownSeconds = Number(button.dataset.cooldown || 60);
        this.url = button.dataset.url;
        this.storageKey = `otp_resend_until_${this.url}`;

        this.timer = null;

        this.initAttemptsCheck();
    }

    initAttemptsCheck() {
        if (this.attemptsDisplay) {
            const attempts = parseInt(this.attemptsDisplay.textContent, 10);
            if (attempts === 0) {
                this.disableSubmit();
            }
        }
    }

    async resend() {
        console.log('Resend OTP code requested');
        if (this.btn.disabled) return;

        this.lock('Sending…');

        try {
            await fetch(this.url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            });

            const until = Date.now() + this.cooldownSeconds * 1000;
            this.storeUntil(until);
            this.startCooldown(until);

            this.resetAttempts();

        } catch (e) {
            console.error(e);
            this.unlock();
            this.setHint('Could not resend code.');
        }
    }

    resetAttempts() {
        if (this.attemptsDisplay) {
            this.attemptsDisplay.textContent = this.maxAttempts;
        }
        this.enableSubmit();
    }

    disableSubmit() {
        if (this.submitBtn) {
            this.submitBtn.disabled = true;
        }
        if (this.codeInput) {
            this.codeInput.disabled = true;
        }
    }

    enableSubmit() {
        if (this.submitBtn) {
            this.submitBtn.disabled = false;
        }
        if (this.codeInput) {
            this.codeInput.disabled = false;
        }
    }

    startCooldown(until) {
        clearInterval(this.timer);
        console.log(`Starting OTP resend cooldown until ${new Date(until).toISOString()}`);
        const tick = () => {
            const remaining = Math.ceil((until - Date.now()) / 1000);

            if (remaining <= 0) {
                this.clearStoredUntil();
                this.unlock();
                clearInterval(this.timer);
            } else {
                this.lock(`Resend in ${remaining}s`);
            }
        };

        tick();
        this.timer = setInterval(tick, 1000);
    }

    lock(text) {
        this.btn.disabled = true;
        this.btn.textContent = text;
    }

    unlock() {
        this.btn.disabled = false;
        this.btn.textContent = 'Resend code';
    }

    setHint(text) {
        if (this.hint) this.hint.textContent = text;
    }

    storeUntil(until) {
        localStorage.setItem(this.storageKey, until);
    }

    getStoredUntil() {
        return Number(localStorage.getItem(this.storageKey));
    }

    clearStoredUntil() {
        localStorage.removeItem(this.storageKey);
    }

    restoreOnRefresh() {
        console.log('Restoring OTP resend cooldown if needed');
        const until = this.getStoredUntil();
        if (until && until > Date.now()) {
            this.startCooldown(until);
        }
    }
}
