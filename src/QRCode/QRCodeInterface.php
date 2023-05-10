<?php

namespace PackBot\QRCode;

use chillerlan\QRCode\QROptions;

interface QRCodeInterface
{
    public function __construct(string $url);

    public function render(): QRCodeImage;

    public function setOptions(QROptions $options): self;
}
