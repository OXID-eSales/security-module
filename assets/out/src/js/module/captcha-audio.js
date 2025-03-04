/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

export class CaptchaAudio {
    constructor(options) {
        this.url = options.url;
        this.fieldTarget = options.fieldTarget;
        this.delayInMilliseconds = 1000;

        this.registerEvents();
    }

    registerEvents() {
        let self = this;
        let requestDelayTimeout;

        this.fieldTarget.addEventListener('click', function() {
            if (typeof requestDelayTimeout === 'undefined' || requestDelayTimeout < Date.now()) {
                self.ajaxRequest();

                requestDelayTimeout = Date.now() + self.delayInMilliseconds;
            }
        });
    }

    ajaxRequest() {
        let self = this;

        fetch(this.url, {
            method: "GET"
        }).then(function(response) {
            return response.arrayBuffer();
        }).then(function(data) {
            self.handleResponse(data);
        }).catch(err => {
        });
    }

    handleResponse(captchaResponse) {
        let blob = new Blob([captchaResponse], { type: "audio/mp3" });
        let blobUrl = URL.createObjectURL(blob);

        let audio = new Audio();
        audio.src = blobUrl;
        audio.controls = false;
        audio.play();
    }
}
