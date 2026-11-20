<?php

namespace ModulesCore\modules\ProductsImport\Services;

use RuntimeException;

class ImageTransferService
{
    private string $sourceFilesUrl;
    private string $targetFilesRoot;
    private bool $sslVerify;
    private string $caBundle;

    public function __construct(
        string $sourceFilesUrl,
        string $targetFilesRoot,
        array $downloadConfig = []
    ) {
        $this->sourceFilesUrl = rtrim($sourceFilesUrl, '/');
        $this->targetFilesRoot = rtrim($targetFilesRoot, DIRECTORY_SEPARATOR);
        $this->sslVerify = (bool) ($downloadConfig['ssl_verify'] ?? true);
        $this->caBundle = (string) ($downloadConfig['ca_bundle'] ?? '');
        if ($this->sslVerify && $this->caBundle !== '' && !is_readable($this->caBundle)) {
            throw new RuntimeException('CA bundle не найден или недоступен: ' . $this->caBundle);
        }
    }

    /** Shared TLS options for both brand logos and product images. */
    private function sslOptions(): array
    {
        $options = [
            CURLOPT_SSL_VERIFYPEER => $this->sslVerify,
            CURLOPT_SSL_VERIFYHOST => $this->sslVerify ? 2 : 0,
        ];
        if ($this->sslVerify && $this->caBundle !== '') {
            $options[CURLOPT_CAINFO] = $this->caBundle;
        }
        return $options;
    }

    /** Transfers a brand logo from the standard OkayCMS files/brands directory.
     * The filename is namespaced to prevent collisions with local brand assets.
     */
    public function transferBrandLogo(string $filename, int $sourceBrandId): string
    {
        $filename = basename(str_replace('\\', '/', $filename));
        if ($filename === '' || $filename === '.' || $filename === '..') {
            throw new RuntimeException('Некорректное имя логотипа бренда');
        }
        $targetName = 'sportfly_' . $sourceBrandId . '_' . $filename;
        $directory = $this->targetFilesRoot . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'brands';
        if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new RuntimeException('Не удалось создать каталог логотипов');
        }
        $target = $directory . DIRECTORY_SEPARATOR . $targetName;
        if (is_file($target) && filesize($target) > 0) return $targetName;
        $url = $this->sourceFilesUrl . '/files/brands/' . rawurlencode($filename);
        $tmp = $target . '.tmp.' . getmypid();
        $handle = fopen($tmp, 'wb');
        if (!$handle) throw new RuntimeException('Не удалось открыть файл логотипа');
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_FILE => $handle,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_FAILONERROR => true,
        ] + $this->sslOptions());
        $ok = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);
        fclose($handle);
        if (!$ok || !is_file($tmp) || filesize($tmp) === 0) {
            @unlink($tmp);
            throw new RuntimeException('Не удалось перенести логотип бренда: ' . $error);
        }
        if (!rename($tmp, $target)) {
            @unlink($tmp);
            throw new RuntimeException('Не удалось сохранить логотип бренда');
        }
        return $targetName;
    }

    /**
     * Переносит один файл изображения.
     *
     * @param string $relativePath Относительный путь из __images
     *
     * @return string
     *
     * @throws RuntimeException
     */
    public function transfer(string $relativePath): string
    {
        $relativePath = ltrim(
            str_replace(['/', '\\'], '/', $relativePath),
            '/'
        );

        if ($relativePath === '') {
            throw new RuntimeException(
                'Не указан относительный путь изображения.'
            );
        }

        $sourceUrl = $this->sourceFilesUrl
            . '/files/originals/'
            . implode(
                '/',
                array_map('rawurlencode', explode('/', $relativePath))
            );

        $targetPath = $this->targetFilesRoot
            . DIRECTORY_SEPARATOR
            . 'files'
            . DIRECTORY_SEPARATOR
            . 'originals'
            . DIRECTORY_SEPARATOR
            . 'products'
            . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

        $targetDirectory = dirname($targetPath);

        if (!is_dir($targetDirectory)) {
            if (!mkdir($targetDirectory, 0777, true) && !is_dir($targetDirectory)) {
                throw new RuntimeException(
                    'Не удалось создать каталог: ' . $targetDirectory
                );
            }
        }

        if (is_file($targetPath)) {
            return 'skipped';
        }

        $handle = fopen($targetPath, 'wb');

        if ($handle === false) {
            throw new RuntimeException(
                'Не удалось создать файл: ' . $targetPath
            );
        }

        $curl = curl_init($sourceUrl);

        curl_setopt_array($curl, [
            CURLOPT_FILE            => $handle,
            CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_CONNECTTIMEOUT  => 10,
            CURLOPT_TIMEOUT         => 60,
            CURLOPT_FAILONERROR     => true,
        ] + $this->sslOptions());

        $success = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);

        curl_close($curl);
        fclose($handle);

        if ($success === false || $httpCode < 200 || $httpCode >= 300) {
            if (is_file($targetPath)) {
                unlink($targetPath);
            }

            throw new RuntimeException(
                'Не удалось скачать изображение. URL: '
                . $sourceUrl
                . ($error !== '' ? '. Ошибка: ' . $error : '')
            );
        }

        return 'transferred';
    }

    public function transferAll(array $images): array
    {
        $results = [
            'total'           => count($images),
            'transferred'     => 0,
            'skipped'         => 0,
            'errors'          => 0,
            'error_messages'  => [],
        ];

        foreach ($images as $image) {
            try {
                $result = $this->transfer($image->filename);

                if ($result === 'transferred') {
                    $results['transferred']++;
                } elseif ($result === 'skipped') {
                    $results['skipped']++;
                }
            } catch (\Throwable $e) {
                $results['errors']++;

                $results['error_messages'][] = 'Файл "'
                    . $image->filename . '": '
                    . $e->getMessage();
            }
        }

        return $results;
    }
}
