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

        $audioData = $this->joinwavs($files);

        return $this->addNoiseToAudio($audioData);
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

    /**
     * @param string $audio
     * @return string
     */
    private function addNoiseToAudio(string $audio): string
    {
        $header = substr($audio, 0, 44);
        $audioData = substr($audio, 44);

        $noiseFile = __DIR__ . '/../../../assets/sounds/noise.wav';
        if (!file_exists($noiseFile)) {
            return '';
        }

        $noiseStream = fopen($noiseFile, 'rb');
        if (!is_resource($noiseStream)) {
            return '';
        }

        // @phpstan-ignore-next-line Ignored because of filesize
        $noiseData = fread($noiseStream, filesize($noiseFile) - 44);
        if (!$noiseData) {
            return '';
        }
        fclose($noiseStream);

        // @phpstan-ignore-next-line Ignored since unpack may return false
        $audioSamples = array_values(unpack('v*', $audioData));
        // @phpstan-ignore-next-line Ignored since unpack may return false
        $noiseSamples = array_values(unpack('v*', $noiseData));

        // add noise to the audio samples
        $outputSamples = [];
        $noiseLength = count($noiseSamples);
        foreach ($audioSamples as $index => $sample) {
            $noiseSample = $noiseSamples[$index % $noiseLength];
            $outputSamples[] = $sample + (int)($noiseSample * 0.15);
        }

        $outputData = pack('v*', ...$outputSamples);

        // update the data chunk size in the header
        $dataSize = strlen($outputData);
        $header = substr_replace($header, pack('V', $dataSize), 40, 4); // update Subchunk2Size
        $header = substr_replace($header, pack('V', 36 + $dataSize), 4, 4); // update ChunkSize

        return $header . $outputData;
    }
}
