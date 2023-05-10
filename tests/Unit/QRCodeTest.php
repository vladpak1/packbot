<?php

namespace PackBot\Tests\Unit;

use chillerlan\QRCode\QROptions;
use PackBot\QRCode\Generators\QRCode;
use PHPUnit\Framework\TestCase;

class QRCodeTest extends TestCase
{
    public function testQRCodeOptions()
    {
        $qrCode     = new QRCode('google.com');
        $newOptions = new QROptions([
            'imageTransparent' => true,
            'scale'            => 10,
        ]);
        $qrCode->setOptions($newOptions);

        $this->assertEquals($newOptions, $qrCode->getOptions());
    }

    public function testQRCodeRender()
    {
        $qrCode = new QRCode('google.com');

        $this->assertIsString($qrCode->render()->getBase64());
    }
}
