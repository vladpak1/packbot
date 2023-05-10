<?php

namespace PackBot\QRCode\Generators;

use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode as QRCodeLib;
use chillerlan\QRCode\QROptions;
use Intervention\Image\ImageManagerStatic as Image;
use PackBot\Path;
use PackBot\QRCode\Exceptions\QRCodeException;
use PackBot\QRCode\QRCodeImage;
use PackBot\QRCode\QRCodeInterface;

class QRCode implements QRCodeInterface
{
    protected string $qrCodeFolder;

    protected QRCodeLib $qrCodeLib;

    protected string $url;

    protected bool $isGenerated = false;

    protected array $defaultOptions = [
        'imageTransparent' => false,
        'scale'            => 15,
        'outputType'       => QROutputInterface::GDIMAGE_PNG,
    ];

    protected QROptions $qrOptions;

    public function __construct(string $url)
    {
        $this->qrCodeFolder = Path::toTemp();
        $this->qrOptions    = new QROptions($this->defaultOptions);
        $this->qrCodeLib    = new QRCodeLib($this->qrOptions);
        $this->url          = $url;

        /**
         * Set Imagick as Intervention driver.
         */
        if (class_exists('Imagick')) {
            Image::configure(['driver' => 'imagick']);
        } else {
            throw new QRCodeException('Imagick is required to generate QR codes.');
        }
    }

    public function render(): QRCodeImage
    {
        return new QRCodeImage($this->qrCodeLib->render($this->url));
    }

    public function setOptions(QROptions $options): self
    {
        $this->qrOptions = $options;
        $this->qrCodeLib->setOptions($options);

        return $this;
    }

    public function getOptions(): QROptions
    {
        return $this->qrOptions;
    }
}
