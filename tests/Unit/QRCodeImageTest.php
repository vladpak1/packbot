<?php

namespace PackBot\Tests\Unit;

use PackBot\Path;
use PackBot\QRCode\QRCodeImage;
use PHPUnit\Framework\TestCase;

class QRCodeImageTest extends TestCase
{
    protected string $exampleBase64PngImage = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABA';

    public function tearDown(): void
    {
        $tempPath = Path::toTemp();

        foreach (glob($tempPath . '/*') as $file) {
            unlink($file);
        }
    }

    public function testWrongBase64()
    {
        $this->expectException(\PackBot\QRCode\Exceptions\QRCodeException::class);
        $this->expectExceptionMessage('Invalid base64 image data.');

        new QRCodeImage('wrongBase64');
    }

    public function testWrongMimeType()
    {
        $this->expectException(\PackBot\QRCode\Exceptions\QRCodeException::class);
        $this->expectExceptionMessage('Invalid image mime type.');

        new QRCodeImage('data:image/jpg;base64,iVBORw0KGgoAAAANSUhEUgAAABA');
    }

    public function testToTemp()
    {
        $qrCodeImage = new QRCodeImage($this->exampleBase64PngImage);
        $tempPath    = $qrCodeImage->toTemp();

        $this->assertFileExists($tempPath);
        $this->assertStringContainsString('qr_', $tempPath);
        $this->assertStringContainsString('.png', $tempPath);
    }

    public function testToFile()
    {
        $qrCodeImage = new QRCodeImage($this->exampleBase64PngImage);
        $tempPath    = $qrCodeImage->toFile(Path::toTemp());

        $this->assertFileExists($tempPath);
        $this->assertStringContainsString('qr_', $tempPath);
        $this->assertStringContainsString('.png', $tempPath);
    }

    public function testGetBase64()
    {
        $qrCodeImage = new QRCodeImage($this->exampleBase64PngImage);

        $this->assertEquals($this->exampleBase64PngImage, $qrCodeImage->getBase64());
    }
}
