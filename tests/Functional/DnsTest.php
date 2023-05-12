<?php

namespace PackBot\Tests\Functional;

use PackBot\Dns;

/**
 * Base class for function command tests.
 */
class DnsTest extends TestWithEnvCase
{
    public function testNotExecuted()
    {
        $this->expectException(\PackBot\DnsException::class);
        $this->expectExceptionMessage('Dns lookup not executed.');
        (new Dns('google.com'))->getRecords();
    }

    public function testDoubleExecute()
    {
        $this->expectException(\PackBot\DnsException::class);
        $this->expectExceptionMessage('Dns lookup already executed.');
        $dns = new Dns('google.com');
        $dns->execute();
        $dns->execute();
    }

    public function testGetRecords()
    {
        $records = (new Dns('google.com'))->execute()->getRecords();

        $this->assertIsArray($records);
        $this->assertNotEmpty($records);
        $this->assertInstanceOf('\PackBot\DnsRecord', $records[0]);
    }
}
