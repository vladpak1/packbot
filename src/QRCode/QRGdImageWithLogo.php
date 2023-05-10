<?php

namespace PackBot\QRCode;

use chillerlan\QRCode\Output\QRCodeOutputException;
use chillerlan\QRCode\Output\QRGdImage;
use Intervention\Image\ImageManagerStatic as Image;
use PackBot\QRCode\Exceptions\QRCodeException;

class QRImageWithLogo extends QRGdImage
{
    /**
     * @throws \chillerlan\QRCode\Output\QRCodeOutputException
     */
    public function dump(string $file = null, string $logo = null): string
    {
        $this->options->returnResource = true;

        if (!is_file($logo) || !is_readable($logo)) {
            throw new QRCodeOutputException('Invalid logo');
        }

        parent::dump($file);

        $logo = Image::make($logo);

        $lw = ((($this->options->logoSpaceWidth - 2) * $this->options->scale)) + 50;
        $lh = ((($this->options->logoSpaceHeight - 2) * $this->options->scale)) + 50;

        $resizedLogo = $this->resizeLogoWithRelayingOnlyToNonTransparentPart($logo, $lw, $lh);

        /**
         * @todo We can add two modes for QRCodes: with and without Imagick
         */
        $tempFile  = tempnam(sys_get_temp_dir(), 'InterventionImage');
        $tempImage = imagepng($this->image, $tempFile, 0);

        if (!$tempImage) {
            throw new QRCodeException('Unable to create temporary image');
        }

        $qrCodeImage = Image::make($tempFile);
        $finalImage  = $qrCodeImage->insert($resizedLogo, 'center');
        $imageData   = $finalImage->encode('image/png');

        return $this->toBase64DataURI($imageData, 'image/png');

    }

    public function resizeLogoWithRelayingOnlyToNonTransparentPart(\Intervention\Image\Image $logo, $width, $height)
    {
        $imageWithoutBackground = $logo->trim('transparent');

        $aspectRatio = $imageWithoutBackground->width() / $imageWithoutBackground->height();

        if ($aspectRatio > 1) {

            $resizedImage = $imageWithoutBackground->widen($width);
        } else {

            $resizedImage = $imageWithoutBackground->heighten($height);
        }

        $resizedImage->resizeCanvas($width, $height, 'center', false, 'rgba(0, 0, 0, 0)');

        return $resizedImage;
    }
}
