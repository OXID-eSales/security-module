<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Captcha\Service;

class CaptchaAudioService implements CaptchaAudioServiceInterface
{
    public function __construct(
        private readonly CaptchaServiceInterface $captchaService
    ) {
    }

    public function generate(): string
    {
        $files = [];

        $captcha = str_split($this->captchaService->getCaptcha());

        foreach ($captcha as $value) {
            $files[] = __DIR__ . '/../../../assets/sounds/' . $value . '.wav';
            $files[] = __DIR__ . '/../../../assets/sounds/silence.wav';
        }

        return $this->joinwavs($files);
    }

    private function joinwavs(array $wavs): string
    {
        $fields = implode('/', [
            'H8ChunkID',
            'VChunkSize',
            'H8Format',
            'H8Subchunk1ID',
            'VSubchunk1Size',
            'vAudioFormat',
            'vNumChannels',
            'VSampleRate',
            'VByteRate',
            'vBlockAlign',
            'vBitsPerSample',
        ]);

        $data = $header = '';
        foreach ($wavs as $wav) {
            $audioStream = fopen($wav, 'rb');
            if (!$audioStream) {
                continue;
            }

            $header = fread($audioStream, 36) ?: '';
            $info = unpack($fields, $header);

            // read optional extra stuff
            if (isset($info['Subchunk1Size']) && $info['Subchunk1Size'] > 16) {
                $header .= fread($audioStream, max(1, (int)$info['Subchunk1Size'] - 16));
            }

            // read SubChunk2ID
            $header .= fread($audioStream, 4);

            // read Subchunk2Size
            $size = unpack('vsize', fread($audioStream, 4) ?: '') ?: [];
            $size = $size['size'];

            // read data
            $data .= fread($audioStream, $size);
        }

        return $header . pack('V', strlen($data)) . $data;
    }
}
