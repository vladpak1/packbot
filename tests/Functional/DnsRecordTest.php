<?php

namespace PackBot\Tests\Functional;

use PackBot\DnsRecord;

/**
 * Base class for function command tests.
 */
class DnsRecordTest extends TestWithEnvCase
{
    public function testEmptyRecord()
    {
        $this->expectException(\PackBot\DnsException::class);
        $this->expectExceptionMessage('Cannot create DnsRecord: empty record data.');
        new \PackBot\DnsRecord([]);
    }

    public function testGetters()
    {
        $record = new DnsRecord([
            'host'     => 'google.com',
            'ttl'      => 1,
            'type'     => 'A',
            'priority' => 1,
        ]);

        $this->assertEquals('google.com', $record->getHost());
        $this->assertEquals(1, $record->getTtl());
        $this->assertEquals('A', $record->getType());
        $this->assertEquals(1, $record->getPriority());
        $this->assertNull($record->getIP());
        $this->assertNull($record->isCloudflare());
    }
}
