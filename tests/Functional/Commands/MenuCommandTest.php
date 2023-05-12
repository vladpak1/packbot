<?php

namespace PackBot\Tests\Functional\Commands;

use PackBot\Tests\Functional\CommandTestCase;
use PackBot\UserSettings;

class MenuCommandTest extends CommandTestCase
{
    public string $command = '/menu';

    public function testCommandImplementsRussian()
    {
        $fakeUpdate = $this->generateUpdateForCommand($this->command);
        $response   = $this->generateSuccessfulTelegramResponse($fakeUpdate);
        $userID     = $fakeUpdate->getMessage()->getFrom()->getId();

        UserSettings::setUserID($userID);
        $settings = new UserSettings();
        $settings->set('language', 'ru_RU');

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
                    'меню',
                    $request_params['form_params']['text']
                ); // Asserting that bot will speak Russian

                $this->assertStringContainsString(
                    'Мониторинг',
                    $this->getFirstButtonOfInlineKeyboard($request_params['form_params']['reply_markup'])['text']
                ); // Asserting that InlineKeyabord is in Russian

                return true;
            })
        )
        ->willReturn($response);

        $this->telegram->processUpdate($fakeUpdate);
    }

    public function testCommandImplementsEnglish()
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
                    'menu',
                    $request_params['form_params']['text']
                ); // Asserting that bot will speak English

                $this->assertStringContainsString(
                    'monitoring',
                    $this->getFirstButtonOfInlineKeyboard($request_params['form_params']['reply_markup'])['text']
                ); // Asserting that InlineKeyabord is in English

                return true;
            })
        )
        ->willReturn($response);

        $this->telegram->processUpdate($fakeUpdate);
    }
}
