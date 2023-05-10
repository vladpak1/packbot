<?php

namespace PackBot\Tests\Unit;

use PackBot\CloudflareChecker;
use PHPUnit\Framework\TestCase;

class CloudflareCheckerTest extends TestCase
{
    public function testIsCloudflare()
    {
        $checker = new CloudflareChecker();

        $knownCloudflareIPs = [
            '104.21.84.128',
            '104.21.80.160',
            '188.114.97.0',
        ];

        foreach ($knownCloudflareIPs as $ip) {
            $this->assertTrue($checker->isCloudflare($ip));
        }
    }

    public function testIsNotCloudflare()
    {
        $checker = new CloudflareChecker();

        $knownNotCloudflareIPs = [
            '142.250.179.206',
            '142.251.36.14',
            '140.82.121.4',
        ];

        foreach ($knownNotCloudflareIPs as $ip) {
            $this->assertFalse($checker->isCloudflare($ip));
        }
    }

    public function testWrongIP()
    {
        $checker = new CloudflareChecker();

        $this->expectException(\PackBot\CloudflareCheckerException::class);
        $checker->isCloudflare('192.168.256.1');
    }
}
