<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\SecurityModule\Captcha\Captcha\Image\Service;

use OxidEsales\SecurityModule\Captcha\Infrastructure\LanguageWrapperInterface;

class CaptchaAudioService implements CaptchaAudioServiceInterface
{
    private const SOUNDS_PATH = __DIR__ . '/../../../../../assets/sounds';

    public function __construct(
        private readonly ImageCaptchaServiceInterface $imageCaptchaService,
        private readonly LanguageWrapperInterface $language
    ) {
    }

    public function generate(): string
    {
        $files = [];

        $captcha = str_split($this->imageCaptchaService->getCaptcha());
        $languageAbbr = $this->language->getCurrentLanguageAbbr();
        $defaultLanguage = 'en';
        $languagePath = realpath(self::SOUNDS_PATH . "/$languageAbbr");
        if (!$languagePath || !is_dir($languagePath) || !$languageAbbr) {
            $languageAbbr = $defaultLanguage;
        }

        foreach ($captcha as $value) {
            $files[] = self::SOUNDS_PATH .
                '/' . $languageAbbr .
                '/' . $this->charFolder($value) .
                '/' . strtolower($value) .
                '.wav';
            $files[] = self::SOUNDS_PATH .
                '/' . $languageAbbr .
                '/silence.wav';
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

            while (!feof($audioStream)) {
                $chunkHeader = fread($audioStream, 8);
                // @phpstan-ignore-next-line Ignored since fread may return false
                if (strlen($chunkHeader) < 8) {
                    break;
                }

                // @phpstan-ignore-next-line Ignored since fread may return false
                $chunk = unpack('A4ChunkID/VChunkSize', $chunkHeader);

                // @phpstan-ignore-next-line Ignored since unpack may return false
                if ($chunk['ChunkID'] === 'data') {
                    // When we find the 'data' chunk, we read the audio data
                    $data .= fread($audioStream, $chunk['ChunkSize']);
                    break;
                }

                fread($audioStream, $chunk['ChunkSize']);
            }
        }

        return $header . "data" . pack('V', strlen($data)) . $data;
    }

    /**
     * @param string $audio
     * @return string
     */
    private function addNoiseToAudio(string $audio): string
    {
        $header = substr($audio, 0, 44);
        $audioData = substr($audio, 44);

        $noiseFile = self::SOUNDS_PATH . '/noise.wav';
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

    private function charFolder(string $char): string
    {
        if (ctype_digit($char)) {
            return 'number';
        } elseif (ctype_upper($char)) {
            return 'upper';
        } elseif (ctype_lower($char)) {
            return 'lower';
        }

        return '';
    }
}
