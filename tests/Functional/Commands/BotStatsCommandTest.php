<?php

namespace PackBot\Tests\Functional\Commands;

use PackBot\Tests\Functional\CommandTestCase;
use PackBot\UserSettings;

class BotStatsCommandTest extends CommandTestCase
{
    public string $command = '/botstats';

    public function testCommandBlockedForUsers()
    {
        $fakeUpdate = $this->generateUpdateForCommand($this->command);
        $response   = $this->generateSuccessfulTelegramResponse($fakeUpdate);
        $userID     = $fakeUpdate->getMessage()->getFrom()->getId();

        UserSettings::setUserID($userID);
        $settings = new UserSettings();
        $settings->set('language', 'en_US');
        $this->client
        ->expects($this->once())
        ->method('post')
        ->with(
            $this->callback(function ($requestString) {
                $expectedString = '/bot' . $this->dummyApiKey . '/sendMessage';
                $this->assertEquals($expectedString, $requestString);

                return true;
            }),
            $this->callback(function ($request_params) {
                $this->assertStringContainsString(
                    'not found',
                    $request_params['form_params']['text']
                );

                return true;
            })
        )
        ->willReturn($response);

        $this->telegram->processUpdate($fakeUpdate);
    }

    public function testCommandWorksForAdmins()
    {
        $fakeUpdate = $this->generateUpdateForCommand($this->command);
        $response   = $this->generateSuccessfulTelegramResponse($fakeUpdate);
        $userID     = $fakeUpdate->getMessage()->getFrom()->getId();

        UserSettings::setUserID($userID);
        $settings = new UserSettings();
        $settings->set('language', 'en_US');

        $this->telegram->enableAdmin($userID);

        $this->client
        ->expects($this->once())
        ->method('post')
        ->with(
            $this->callback(function ($requestString) {
                $expectedString = '/bot' . $this->dummyApiKey . '/sendMessage';
                $this->assertEquals($expectedString, $requestString);

                return true;
            }),
            $this->callback(function ($request_params) {
                $this->assertStringContainsString(
                    'Object',
                    $request_params['form_params']['text']
                );

                return true;
            })
        )
        ->willReturn($response);

        $this->telegram->processUpdate($fakeUpdate);
    }
}
