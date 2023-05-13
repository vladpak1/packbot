<?php

namespace PackBot\Tests\Functional\Screens;

use PackBot\Tests\Functional\ScreenTestCase;
use PackBot\MainMenuScreen;

class MainMenuScreenTest extends ScreenTestCase
{
    public function testMonitoringCallback()
    {
        $this->client
        ->expects($this->exactly(2))
        ->method('post')
        ->withConsecutive(
            // first call arguments
            [$this->callback(function ($requestString) {
                $this->assertStringContainsString('sendMessage', $requestString);

                return true;
            }), $this->callback(function ($request_params) {
                $this->assertStringContainsString('Sites you are', $request_params['form_params']['text']);
                $this->assertEquals(1, $request_params['form_params']['chat_id']);

                return true;
            })],
            // second call arguments
            [$this->callback(function ($requestString) {
                $this->assertStringContainsString('answerCallbackQuery', $requestString);

                return true;
            }), $this->callback(function ($request_params) {
                $this->assertEquals(1, $request_params['form_params']['callback_query_id']);

                return true;
            })]
        )
        ->willReturnOnConsecutiveCalls(
            $this->generateSuccessfulTelegramResponse($this->generateFakeUpdate()),
            $this->generateSuccessfulTelegramResponse($this->generateFakeUpdate())
        );

        $screen = $this->instantiateScreen($this->instantiateCommand(), '\\' . MainMenuScreen::class);
        $screen->executeCallback('siteMonitoring');
    }
}
