<?php

namespace PackBot\Tests\Functional;

use GuzzleHttp\Psr7\Response;
use Longman\TelegramBot\Commands\Command;
use Longman\TelegramBot\Commands\SystemCommands\CallbackqueryCommand;
use Longman\TelegramBot\Entities\Update;
use PackBot\UserSettings;

/**
 * Class ScreenTestCase.
 *
 * This class is a TestCase for all Screens tests.
 * It provides some useful methods to instantiate a Screen and a Command.
 */
abstract class ScreenTestCase extends TestWithEnvCase
{
    public function setUp(): void
    {
        parent::setUp();
        UserSettings::setUserID(1);

        $setting = new UserSettings();

        $setting->set('language', 'en_US');

        /**
         * Simulate a user interaction with the bot.
         * It is necessary to make some features work (e.g conversations).
         */
        $this->simulateInteraction();
    }

    protected function simulateInteraction()
    {
        $fakeUpdate = new Update([
            'update_id' => 1,
            'message'   => [
                'message_id' => 1,
                'from'       => [
                    'id'            => 1,
                    'is_bot'        => false,
                    'first_name'    => 'Test',
                    'last_name'     => 'User',
                    'username'      => 'testuser',
                    'language_code' => 'en',
                ],
                'chat' => [
                    'id'         => 1,
                    'first_name' => 'Test',
                    'last_name'  => 'User',
                    'username'   => 'testuser',
                    'type'       => 'private',
                ],
                'date' => 1,
                'text' => 'dfgdfgdfg',
            ],
        ]);

        UserSettings::setUserID(1);

        $this->telegram->processUpdate($fakeUpdate);

    }

    protected function instantiateScreen(Command $command, string $screenClass): \PackBot\Screen
    {
        return new $screenClass($command);
    }

    protected function generateSuccessfulTelegramResponse(Update $update): Response
    {
        return new Response(200, [], $update->toJson());
    }

    protected function instantiateCommand(): CallbackqueryCommand
    {
        return new CallbackqueryCommand($this->telegram, $this->generateFakeUpdate());
    }

    protected function generateFakeUpdate(): Update
    {
        return new Update([
            'update_id' => 1,
            'message'   => [
                'message_id' => 1,
                'from'       => [
                    'id'            => 1,
                    'is_bot'        => false,
                    'first_name'    => 'Test',
                    'last_name'     => 'User',
                    'username'      => 'testuser',
                    'language_code' => 'en',
                ],
                'chat' => [
                    'id'         => 1,
                    'first_name' => 'Test',
                    'last_name'  => 'User',
                    'username'   => 'testuser',
                    'type'       => 'private',
                ],
                'date' => 1,
                'call',
            ],
            'callback_query' => [
                'id'   => 1,
                'from' => [
                    'id'            => 1,
                    'is_bot'        => false,
                    'first_name'    => 'Test',
                    'last_name'     => 'User',
                    'username'      => 'testuser',
                    'language_code' => 'en',
                ],
                'message' => [
                    'message_id' => 1,
                    'from'       => [
                        'id'            => 1,
                        'is_bot'        => false,
                        'first_name'    => 'Test',
                        'last_name'     => 'User',
                        'username'      => 'testuser',
                        'language_code' => 'en',
                    ],
                    'chat' => [
                        'id'         => 1,
                        'first_name' => 'Test',
                        'last_name'  => 'User',
                        'username'   => 'testuser',
                        'type'       => 'private',
                    ],
                    'date' => 1,
                ],
                'chat_instance'     => '1',
                'data'              => 'siteMonitoring',
                'inline_message_id' => '1',
            ],
        ]);
    }
}
