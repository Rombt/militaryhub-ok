<?php

namespace ProductsImport\Services;

use RuntimeException;

class ImageTransferService
{
    private string $sourceFilesUrl;
    private string $targetFilesRoot;

    public function __construct(
        string $sourceFilesUrl,
        string $targetFilesRoot
    ) {
        $this->sourceFilesUrl = rtrim($sourceFilesUrl, '/');
        $this->targetFilesRoot = rtrim($targetFilesRoot, DIRECTORY_SEPARATOR);
    }

    /**
     * Переносит один файл изображения.
     *
     * @param string $relativePath Относительный путь из db_images
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
            // CURLOPT_SSL_VERIFYPEER  => true,
            // CURLOPT_SSL_VERIFYHOST  => 2,
            // для локальной разработки
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,

            CURLOPT_RESOLVE        => [
                'sportfly.com.ua:443:159.69.87.214',
            ],
        ]);

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
