<?php

namespace PackBot\QRCode\Generators;

use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QROptions;
use Intervention\Image\ImageManagerStatic as Image;
use PackBot\QRCode\ColorHelper;
use PackBot\QRCode\Exceptions\QRCodeException;
use PackBot\QRCode\QRCodeImage;
use PackBot\QRCode\QRCodeInterface;
use PackBot\QRCode\QRImageWithLogo;

final class QRCodeWithLogoColored extends QRCodeWithLogo implements QRCodeInterface
{
    protected string $logoPath;

    protected string $url;

    public function __construct(string $url)
    {
        $this->url = $url;
        parent::__construct($this->url);
    }

    public function render(): QRCodeImage
    {

        $tempLogo       = Image::make($this->logoPath);
        $dominantColors = ColorHelper::getMostCommonColors($tempLogo);
        $finderColor    = ColorHelper::getALittleDarkerColor($dominantColors[1]);

        $rgbFinderColor = ColorHelper::hexToRgbArray($finderColor);

        /**
         * If the finder color is too light, the QR code will not be readable.
         * So we replace it to a darker color.
         */
        $i = 0;

        while (ColorHelper::isTooLight($rgbFinderColor)) {
            $finderColor    = ColorHelper::getALittleDarkerColor($finderColor);
            $rgbFinderColor = ColorHelper::hexToRgbArray($finderColor);
            $i++;

            if ($i > 10) {
                throw new QRCodeException('Could not find a suitable finder color after 10 tries. Please try again.');
            }
        }

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
            'moduleValues'     => [
                QRMatrix::M_FINDER_DOT  => $rgbFinderColor,
                QRMatrix::M_FINDER_DARK => $rgbFinderColor,
            ],
        ]);

        $this->setOptions($newOptions);

        $this->qrCodeLib->addByteSegment($this->url);

        $matrix = $this->qrCodeLib->getQRMatrix();
        $image  = new QRImageWithLogo($this->qrOptions, $matrix);

        $base64 = $image->dump(null, $this->logoPath);

        return new QRCodeImage($base64);
    }
}
