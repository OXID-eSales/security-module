/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

export class CaptchaAudio {
    constructor(options) {
        this.url = options.url;

        this.ajaxRequest();
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
        let blob = new Blob([captchaResponse], { type: "audio/wav" });
        let blobUrl = URL.createObjectURL(blob);

        let audio = new Audio();
        audio.src = blobUrl;
        audio.controls = false;
        audio.play();
    }
}
