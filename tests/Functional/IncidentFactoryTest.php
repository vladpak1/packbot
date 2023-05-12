<?php

namespace PackBot\Tests\Functional;

use PackBot\IncidentFactory;

/**
 * Base class for function command tests.
 */
class IncidentFactoryTest extends TestWithEnvCase
{
    public function testIncidentForNonExistingSite()
    {
        $this->expectException(\PackBot\IncidentException::class);
        $this->expectExceptionMessage('Site with ID 100 does not exist.');
        IncidentFactory::createIncident(100, []);
    }
}
