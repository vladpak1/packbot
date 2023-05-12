<?php

namespace PackBot\Tests\Functional;

use PackBot\IndexPossibility;

class IndexPossibilityTest extends TestWithEnvCase
{
    public function testEmptyDomain()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Domain cannot be empty.');
        $indexPossibility = new IndexPossibility('');
    }

    public function testNotExecuted()
    {
        $this->expectException(\PackBot\IndexPossibilityException::class);
        $this->expectExceptionMessage('Cannot get response before execution.');
        $indexPossibility = new IndexPossibility('example.com');
        $indexPossibility->getResponse();
    }

    public function testResponse()
    {
        $indexPossibilityResponse = (new IndexPossibility('google.com'))->execute()->getResponse();

        $this->assertInstanceOf(\PackBot\IndexPossibilityResponse::class, $indexPossibilityResponse);
        $this->assertFalse($indexPossibilityResponse->isIndexBlocked());
        $this->assertFalse($indexPossibilityResponse->isIndexBlockedByPage());
        $this->assertFalse($indexPossibilityResponse->isIndexBlockedByRobots());
        $this->assertTrue($indexPossibilityResponse->isOK());
    }
}
