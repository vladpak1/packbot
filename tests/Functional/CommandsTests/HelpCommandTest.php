<?php

namespace PackBot\Tests\Functional;

use PackBot\UserSettings;

class HelpCommandTest extends CommandTestCase {

    public string $command = '/help';

    public function testCommandImplementsRussian() {
        $fakeUpdate = $this->generateUpdateForCommand($this->command);
        $response   = $this->generateSuccessfulTelegramResponse($fakeUpdate);
        $userID     = $fakeUpdate->getMessage()->getFrom()->getId();

        UserSettings::setUserID($userID);
        $settings = new UserSettings($userID);
        $settings->set('language', 'ru_RU');

        $this->client
        ->expects($this->once())
        ->method('post')
        ->with($this->callback(function ($requestString) {
            $expectedString = '/bot' . $this->dummyApiKey . '/sendMessage';
            $this->assertEquals($expectedString, $requestString);

            return true;
        }),
        $this->callback(function ($request_params) {
            $this->assertStringContainsString('команды',
            $request_params['form_params']['text']);

            return true;
        }))
        ->willReturn($response);

        $this->telegram->processUpdate($fakeUpdate);
    }

    public function testCommandImplementsEnglish() {
        $fakeUpdate = $this->generateUpdateForCommand($this->command);
        $response   = $this->generateSuccessfulTelegramResponse($fakeUpdate);
        $userID     = $fakeUpdate->getMessage()->getFrom()->getId();

        UserSettings::setUserID($userID);
        $settings = new UserSettings($userID);
        $settings->set('language', 'en_US');

        $this->client
        ->expects($this->once())
        ->method('post')
        ->with($this->callback(function ($requestString) {
            $expectedString = '/bot' . $this->dummyApiKey . '/sendMessage';
            $this->assertEquals($expectedString, $requestString);

            return true;
        }),
        $this->callback(function ($request_params) {
            $this->assertStringContainsString('commands',
            $request_params['form_params']['text']);

            return true;
        }))
        ->willReturn($response);

        $this->telegram->processUpdate($fakeUpdate);
    }
}
