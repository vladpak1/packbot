<?php

namespace PackBot\Tests\Unit;

use PackBot\Curl;
use PHPUnit\Framework\TestCase;

class CurlTest extends TestCase
{
    public function testOKResponse()
    {
        $response = (new Curl('google.com'))->execute()->getResponse();

        $this->assertEquals(200, $response->getCode());
        $this->assertTrue($response->isOK());
    }

    public function testNotFoundResponse()
    {
        $response = (new Curl('google.com/this-page-does-not-exist'))->execute()->getResponse();

        $this->assertEquals(404, $response->getCode());
        $this->assertFalse($response->isOK());
    }

    public function testGetResponseWithoutExecuting()
    {
        $this->expectException(\PackBot\CurlException::class);
        $this->expectExceptionMessage('Cannot get response: curl request is not executed.');
        (new Curl('google.com'))->getResponse();
    }

    public function testDoubleExecute()
    {
        $this->expectException(\PackBot\CurlException::class);
        $this->expectExceptionMessage('Cannot execute curl request: curl request is already executed.');
        $curl = new Curl('google.com');
        $curl->execute();
        $curl->execute();
    }

    public function testEmptyCurlError()
    {
        $this->assertEmpty((new Curl('google.com'))->getCurlError());
    }

    public function testCurlSettings()
    {
        $curl = new Curl('google.com');
        $curl->setTimeout(1);
        $curl->setFollowLocation(true);
        $curl->setHeaders([
            'User-Agent: NoPackBot',
        ]);

        $options = $curl->getOptions();

        $this->assertEquals(1, $options['CURLOPT_TIMEOUT']);
        $this->assertEquals(true, $options['CURLOPT_FOLLOWLOCATION']);
        $this->assertEquals(['User-Agent: NoPackBot'], $options['CURLOPT_HTTPHEADER']);
    }
}
