<?php

namespace PackBot\Tests\Functional;

use PackBot\Path;

/**
 * Here's universal test for all Screens.
 */
final class GeneralScreenTest extends ScreenTestCase
{
    /**
     * @dataProvider screenProvider
     */
    public function testWrongCallback(string $screenClass)
    {
        $response = $this->generateSuccessfulTelegramResponse($this->generateFakeUpdate());

        $this->client
        ->method('post')
        ->with($this->callback(function ($requestString) {

            $this->assertStringContainsString('answerCallbackQuery', $requestString);

            return true;
        }), $this->callback(function ($request_params) {

            $this->assertEquals($request_params['form_params']['callback_query_id'], 1);
            $this->assertStringContainsString('Something went wrong', $request_params['form_params']['text']);

            return true;
        }))
        ->willReturn($response);

        $screen = $this->instantiateScreen($this->instantiateCommand(), $screenClass);
        $screen->executeCallback('wrongCallback');
    }

    /**
     * @dataProvider screenProvider
     */
    public function testConsructor(string $screenClass)
    {
        $screen = $this->instantiateScreen($this->instantiateCommand(), $screenClass);
        $this->assertIsObject($screen);
    }

    public function screenProvider(): array
    {
        /**
         * Let's scan the screens directory to find all screen
         * classes and run generic tests against them.
         */
        $screensFolder = Path::toRoot() . '/src/Screen/screens/';
        $screenClasses = [];

        foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($screensFolder)) as $file) {
            if ($file->isDir()) {
                continue;
            }

            if ('php' !== $file->getExtension()) {
                continue;
            }

            $screenClass     = '\\PackBot\\' . str_replace('.php', '', basename($file->getRealPath()));
            $screenClasses[] = [$screenClass];
        }

        return $screenClasses;
    }
}
