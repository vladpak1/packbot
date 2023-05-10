<?php

namespace PackBot\Tests\Unit;

use PackBot\CloudflareChecker;
use PHPUnit\Framework\TestCase;

class CloudflareCheckerTest extends TestCase
{
    protected array $ranges = [
        '173.245.48.0/20',
        '103.21.244.0/22',
        '103.31.4.0/22',
        '141.101.64.0/18',
        '108.162.192.0/18',
        '190.93.240.0/20',
        '188.114.96.0/20',
        '197.234.240.0/22',
        '198.41.128.0/17',
        '162.158.0.0/15',
        '104.16.0.0/13',
        '104.24.0.0/14',
        '172.64.0.0/13',
        '131.0.72.0/22',
    ];

    public function testIsCloudflare()
    {
        $checker = new CloudflareChecker();
        $checker->setRanges($this->ranges);

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
        $checker->setRanges($this->ranges);

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
