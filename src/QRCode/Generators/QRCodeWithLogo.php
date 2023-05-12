<?php

namespace PackBot\QRCode\Generators;

use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QROptions;
use PackBot\QRCode\Exceptions\InvalidLogoException;
use PackBot\QRCode\QRCodeImage;
use PackBot\QRCode\QRCodeInterface;
use PackBot\QRCode\QRImageWithLogo;
use PackBot\TempFile;

class QRCodeWithLogo extends QRCode implements QRCodeInterface
{
    protected string $logoPath;

    protected string $url;

    public function __construct(string $url)
    {
        $this->url = $url;
        parent::__construct($this->url);
    }

    public function setLogo(string $logo): self
    {
        $this->logoPath = $logo;
        $this->checkLogo();

        return $this;
    }

    protected function checkLogo(): void
    {
        if (!file_exists($this->logoPath)) {
            throw new InvalidLogoException('Logo file not found.');
        }

        if (!is_readable($this->logoPath)) {
            throw new InvalidLogoException('Logo file is not readable.');
        }

        if (!in_array(TempFile::getMimeType($this->logoPath), ['image/png', 'image/jpeg'])) {
            throw new InvalidLogoException(sprintf('Invalid logo file. Currently only PNG and JPEG files are supported, you provided "%s".', TempFile::getMimeType($this->logoPath)));
        }
    }

    public function render(): QRCodeImage
    {

        $newOptions = new QROptions([
            'eccLevel'         => EccLevel::H,
            'scale'            => 15,
            'version'          => 5,
            'addLogoSpace'     => true,
            'logoSpaceWidth'   => 15,
            'logoSpaceHeight'  => 15,
            'keepAsSquare'     => [QRMatrix::M_FINDER, QRMatrix::M_FINDER_DOT],
            'imageBase64'      => true,
            'outputType'       => QROutputInterface::GDIMAGE_PNG,
            'imageTransparent' => false,
            'quietzoneSize'    => 1,
        ]);

        $this->setOptions($newOptions);

        $this->qrCodeLib->addByteSegment($this->url);

        $matrix = $this->qrCodeLib->getQRMatrix();
        $image  = new QRImageWithLogo($this->qrOptions, $matrix);

        $base64 = $image->dump(null, $this->logoPath);

        return new QRCodeImage($base64);
    }
}
