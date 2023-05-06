<?php

namespace PackBot\Tests\Functional;

use PackBot\UserSettings;

class StartCommandTest extends CommandTestCase {

    public string $command = '/start';

    public function testBotSendsLanguageChoiceScreen() {
        $fakeUpdate = $this->generateUpdateForCommand($this->command);
        $response   = $this->generateSuccessfulTelegramResponse($fakeUpdate);


        $this->client
        ->expects($this->once())
        ->method('post')
        ->with($this->callback(function ($requestString) {
            $expectedString = '/bot' . $this->dummyApiKey . '/sendMessage';
            $this->assertEquals($expectedString, $requestString);
            return true;
            
        }), $this->callback(function ($request_params) {
            $this->assertStringContainsString('language', $request_params['form_params']['text']); // Asserting that bot will send LanguageChoiseScreen
            return true;
        }))
        ->willReturn($response);

        $this->telegram->processUpdate($fakeUpdate);
    }

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
            $this->assertStringContainsString('меню',
            $request_params['form_params']['text']); // Asserting that bot will speak Russian

            $this->assertStringContainsString('Мониторинг',
            $this->getFirstButtonOfInlineKeyboard($request_params['form_params']['reply_markup'])['text']); // Asserting that InlineKeyabord is in Russian

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
            $this->assertStringContainsString('menu',
            $request_params['form_params']['text']); // Asserting that bot will speak English

            $this->assertStringContainsString('monitoring',
            $this->getFirstButtonOfInlineKeyboard($request_params['form_params']['reply_markup'])['text']); // Asserting that InlineKeyabord is in English

            return true;
        }))
        ->willReturn($response);

        $this->telegram->processUpdate($fakeUpdate);
    }
}
