<?php

namespace PackBot\Tests\Unit;

use PackBot\Curl;
use PHPUnit\Framework\TestCase;

class CurlTest extends TestCase {

    public function testOKResponse() {
        $response = (new Curl('google.com'))->execute()->getResponse();

        $this->assertEquals(200, $response->getCode());
        $this->assertTrue($response->isOK());
    }

    public function testNotFoundResponse() {
        $response = (new Curl('google.com/this-page-does-not-exist'))->execute()->getResponse();

        $this->assertEquals(404, $response->getCode());
        $this->assertFalse($response->isOK());
    }

    public function testGetResponseWithoutExecuting() {
        $this->expectException(\PackBot\CurlException::class);
        $this->expectExceptionMessage('Cannot get response: curl request is not executed.');
        (new Curl('google.com'))->getResponse();
    }
}