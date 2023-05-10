<?php

namespace PackBot\Tests\Functional;

use GuzzleHttp\Client;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;
use PackBot\Environment;
use PackBot\PackDB;
use PackBot\Path;
use PHPUnit\Framework\TestCase;

/**
 * Base class for function command tests.
 */
abstract class TestWithEnvCase extends TestCase
{
    public string $dummyApiKey = '123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11';

    protected Telegram $telegram;

    protected Client $client;

    protected array $credentials = [];

    public function setUp(): void
    {

        defined('TESTSUITE') || define('TESTSUITE', true);

        $this->credentials = [
            'host'     => Environment::var('db_host'),
            'user'     => Environment::var('db_user'),
            'password' => Environment::var('db_password'),
            'database' => Environment::var('db_name'),
        ];

        try {
            PackDB::connect();
        } catch (\Throwable $e) {
            $this->markTestSkipped('This test requires a database connection. Error: ' . $e->getMessage());
        }

        TestHelpers::emptyDB($this->credentials);

        $telegram = new Telegram($this->dummyApiKey, 'testbot');
        $telegram->enableMySql($this->credentials);
        $commandsPaths = [
            Path::toRoot() . '/Commands',
        ];

        $telegram->addCommandsPaths($commandsPaths);
        $this->telegram = $telegram;

        /**
         * @var Client $client
         */
        $this->client = $this->getMockBuilder(Client::class)->getMock();
        Request::setClient($this->client);
    }

    public function tearDown(): void
    {
        TestHelpers::emptyDB($this->credentials);
    }
}
