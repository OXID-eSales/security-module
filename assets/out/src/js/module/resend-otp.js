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
        } = options;

        this.btn = button;
        this.hint = document.getElementById(hintId);
        this.submitBtn = document.getElementById(submitButtonId);
        this.attemptsDisplay = document.getElementById(attemptsDisplayId);
        this.codeInput = document.getElementById(codeInputId);

        this.cooldownSeconds = Number(button.dataset.cooldown || 60);
        this.url = button.dataset.url;
        this.textDefault = button.dataset.textDefault;
        this.textSending = button.dataset.textSending;
        this.textError = button.dataset.textError;
        this.textCountdown = button.dataset.textCountdown;
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
        if (this.btn.disabled) return;

        this.lock(this.textSending);

        try {
            const response = await fetch(this.url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            });

            if (response.status === 429) {
                const until = Date.now() + this.cooldownSeconds * 1000;
                this.storeUntil(until);
                this.startCooldown(until);
                return;
            }

            if (!response.ok) {
                this.unlock();
                this.setHint(this.textError);
                return;
            }

            const data = await response.json();
            const until = Date.now() + this.cooldownSeconds * 1000;
            this.storeUntil(until);
            this.startCooldown(until);
            this.resetAttempts(data.remainingAttempts);

        } catch (e) {
            console.error(e);
            this.unlock();
            this.setHint(this.textError);
        }
    }

    resetAttempts(count) {
        if (this.attemptsDisplay) {
            this.attemptsDisplay.textContent = count;
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
        const tick = () => {
            const remaining = Math.ceil((until - Date.now()) / 1000);

            if (remaining <= 0) {
                this.clearStoredUntil();
                this.unlock();
                clearInterval(this.timer);
            } else {
                this.lock(this.textCountdown.replace('%d', remaining));
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
        this.btn.textContent = this.textDefault;
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
        const stored = this.getStoredUntil();
        if (stored && stored > Date.now()) {
            this.startCooldown(stored);
            return;
        }

        const serverRemaining = Number(this.btn.dataset.cooldownRemaining || 0);
        if (serverRemaining > 0) {
            const until = Date.now() + serverRemaining * 1000;
            this.storeUntil(until);
            this.startCooldown(until);
        }
    }
}
