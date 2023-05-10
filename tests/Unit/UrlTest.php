<?php

namespace PackBot\Tests\Unit;

use PackBot\Url;
use PHPUnit\Framework\TestCase;

class UrlTest extends TestCase
{
    public function testIsValid()
    {
        $validUrls = [
            'https://example.com',
            'http://example.com',
            'https://example.com/',
            'example.com',
            'example.com:80',
            'example.com:8080 ',
        ];

        foreach ($validUrls as $url) {
            $this->assertTrue(Url::isValid($url), sprintf('Url "%s" is valid, but Url::isValid returned false.', $url));
        }

        $invalidUrls = [
            'example',
            'e',
            'example.com:8080:8080',
            'google. com',
            '',
        ];

        foreach ($invalidUrls as $url) {
            $this->assertFalse(Url::isValid($url), sprintf('Url "%s" is invalid, but Url::isValid returned true.', $url));
        }
    }

    public function testGetDomain()
    {
        $this->assertEquals('example.com', Url::getDomain('https://example.com'));
        $this->assertEquals('example.com', Url::getDomain('http://example.com'));
        $this->assertEquals('example.com', Url::getDomain('https://example.com/'));
        $this->assertEquals('example.com', Url::getDomain('example.com/1/2'));
        $this->assertEquals('example.com', Url::getDomain('example.com:80'));
    }

    public function removeWWW()
    {
        $this->assertEquals('example.com', Url::removeWWW('https://www.example.com'));
        $this->assertEquals('example.com', Url::removeWWW('http://www.example.com'));
        $this->assertEquals('example.com', Url::removeWWW('https://www.example.com/'));
        $this->assertEquals('example.com', Url::removeWWW('www.example.com/1/2'));
        $this->assertEquals('example.com', Url::removeWWW('www.example.com:80'));
    }
}
