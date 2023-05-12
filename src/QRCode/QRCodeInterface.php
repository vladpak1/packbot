<?php

namespace PackBot\QRCode;

use chillerlan\QRCode\QROptions;

interface QRCodeInterface
{
    /**
     * QRCodeInterface constructor.
     * This class is a wrapper for the chillerlan/php-qrcode library.
     *
     * @param  string                                          $url The URL to encode in the QR code.
     * @throws \PackBot\QRCode\Exceptions\QRCodeException
     * @throws \PackBot\QRCode\Exceptions\InvalidLogoException
     */
    public function __construct(string $url);

    /**
     * Render the QR code.
     * Returns a QRCodeImage object (which is a wrapper for base64 encoded image data).
     */
    public function render(): QRCodeImage;

    /**
     * Set the options for the QR code as a QROptions object.
     */
    public function setOptions(QROptions $options): self;

    /**
     * Get the options for the QR code as a QROptions object.
     */
    public function getOptions(): QROptions;
}
