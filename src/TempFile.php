<?php

namespace PackBot;

use Intervention\MimeSniffer\MimeSniffer;
use Longman\TelegramBot\Request;
use Throwable;

class TempFile
{
    /**
     * Creating temp png image from base64 string.
     *
     * @param  string    $base64 Image as base64 string.
     * @throws Exception If cannot create temp image.
     * @return string    Path to temp image.
     */
    public static function base64ToImg(string $base64): string
    {
        $path    = Path::toTemp() . '/' . uniqid() . '.png';
        $data    = explode(',', $base64);
        $data    = base64_decode($data[1]);
        $success = file_put_contents($path, $data);

        if (false === $success) {
            throw new \Exception('Cannot create temp image.');
        }

        return $path;
    }

    /**
     * Creating temp txt file based on string.
     *
     * @param  string    $content Content of txt file.
     * @param  string    $name    Name of txt file. If not specified, will be generated uniqid.
     * @throws Exception If cannot create temp txt file.
     * @return string    Path to temp txt file.
     */
    public static function txt(string $content, $name = ''): string
    {
        $path    = Path::toTemp() . '/' . ($name ?: uniqid()) . '.txt';
        $success = file_put_contents($path, $content);

        if (false === $success) {
            throw new \Exception('Cannot create temp txt file.');
        }

        return $path;
    }

    /**
     * Downloading file from Telegram by fileID.
     * Note that this method will not work if the file is more than 20 MB (telegram restriction).
     *
     * Should ONLY be used with additional security checks.
     *
     * @param  string    $fileID FileID of file.
     * @throws Exception If cannot download file.
     * @return string    Path to downloaded file.
     */
    public static function downloadFileFromTelegram(string $fileID): string
    {
        try {
            $fileObject = Request::getFile([
                'file_id' => $fileID,
            ]);

            if (!$fileObject->isOk()) {
                throw new \Exception('Cannot download file: ' . $fileID);
            }

            if ($fileObject->getFileSize() > 1e+20) {
                throw new \Exception('File is too big: ' . $fileID);
            }

            $fileObject = $fileObject->getResult();
            $file       = Request::downloadFile($fileObject);

            $path = Path::toTemp() . '/' . $fileObject->getFilePath();
        } catch (Throwable $e) {
            throw new \Exception($e);
        }

        return $path;
    }

    /**
     * Downloading txt file from Telegram by fileID.
     * Note that this method will not work if the file is more than 20 MB (telegram restriction).
     *
     * @param  string    $fileID FileID of file.
     * @throws Exception If cannot download file or file is not txt.
     * @return string    Content of txt file.
     */
    public static function getTextFromFileFromTelegram(string $fileID)
    {
        $file = self::downloadFileFromTelegram($fileID);

        /**
         * Check file extension.
         */
        $fileInfo = pathinfo($file);

        if ('txt' != $fileInfo['extension']) {
            throw new \Exception('File is not txt: ' . $fileID);
        }

        /**
         * Check file mime type.
         */
        $mimeType = self::getMimeType($file);

        if ('text/plain' != $mimeType) {
            throw new \Exception('File is not txt: ' . $fileID);
        }

        /**
         * Get file content.
         */
        $content = file_get_contents($file);

        if (false === $content) {
            throw new \Exception('Cannot get file content: ' . $fileID);
        }

        return $content;
    }

    public static function getMimeType(string $path): string
    {
        $sniffer = MimeSniffer::createFromFilename($path);
        $type    = $sniffer->getType();

        return (string) $type;
    }
}
