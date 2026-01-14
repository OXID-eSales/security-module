/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

export class ResendOtp {
    constructor(button, { hintId = 'resend-hint' } = {}) {
        this.btn = button;
        this.hint = document.getElementById(hintId);

        this.cooldownSeconds = Number(button.dataset.cooldown || 60);
        this.url = button.dataset.url;
        this.storageKey = `otp_resend_until_${this.url}`;

        this.timer = null;
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

        } catch (e) {
            console.error(e);
            this.unlock();
            this.setHint('Could not resend code.');
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

    restoreIfNeeded() {
        console.log('Restoring OTP resend cooldown if needed');
        const until = this.getStoredUntil();
        if (until && until > Date.now()) {
            this.startCooldown(until);
        }
    }
}
