<?php

namespace PackBot\QRCode;

use PackBot\Entity;
use PackBot\Path;
use PackBot\QRCode\Exceptions\QRCodeException;

class QRCodeImage extends Entity implements \JsonSerializable
{
    protected string $base64ImageData;

    protected string $base64RawData;

    /**
     * This class is representing a QRCode image (base64 encoded string).
     *
     * @method string toFile(string $path) Save the image to a file.
     * @method string toTemp()             Save the image to a temporary folder.
     * @method string toImage()            Return the image as a base64 encoded string..
     */
    public function __construct(string $base64ImageData)
    {
        $this->base64ImageData = $this->prepareBase64($base64ImageData);
        $this->base64RawData   = $base64ImageData;
    }

    public function toFile(string $path): string
    {
        $path      = Path::toTemp();
        $fileName  = '/qr_' . uniqid() . '.png';
        $imageData = $this->base64ImageData;

        if (!file_put_contents($path . $fileName, $imageData)) {
            throw new QRCodeException('Failed to save QRCode image.');
        }

        return $path . $fileName;
    }

    public function toTemp(): string
    {
        $tempPath = Path::toTemp();

        return $this->toFile($tempPath);
    }

    public function jsonSerialize(): string
    {
        return json_encode($this->base64RawData);
    }

    public function __toString(): string
    {
        return $this->base64RawData;
    }

    public function getRawData(): object
    {
        return (object) [
            'base64ImageData' => $this->base64ImageData,
            'base64RawData'   => $this->base64RawData,
        ];
    }

    public function getBase64(): string
    {
        return $this->base64RawData;
    }

    private function prepareBase64(string $base64Image): string
    {
        if (!preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
            throw new QRCodeException('Invalid base64 image data.');
        }

        $type = strtolower($type[1]);

        if (!in_array($type, ['png'])) {
            throw new QRCodeException('Invalid image mime type.');
        }

        $encodedImg = str_replace('data:image/png;base64,', '', $base64Image);

        return base64_decode($encodedImg);
    }
}
