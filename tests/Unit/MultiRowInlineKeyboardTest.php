<?php

namespace PackBot\Tests\Unit;

use PackBot\MultiRowInlineKeyboard;
use PHPUnit\Framework\TestCase;

class MultiRowInlineKeyboardTest extends TestCase
{
    public function testWrongSyntas()
    {
        $keyboard = new MultiRowInlineKeyboard([
            ['text' => '1'],
            ['text' => '2'],
            ['text' => '3'],
            ['text' => '4'],
            ['text' => '5'],
            ['text' => '6'],
            ['text' => '7'],
            ['text' => '8'],
            ['text' => '9'],
            ['text' => '10'],
        ]);

        $this->assertEmpty($keyboard->getProperty('inline_keyboard'));
    }

    public function testAutoLines()
    {
        $keyboard = new MultiRowInlineKeyboard(
            [
            [
                'text'          => '1',
                'callback_data' => '1',
            ],
            [
                'text'          => '2',
                'callback_data' => '2',
            ],
            [
                'text'          => '3',
                'callback_data' => '3',
            ],
        ],
            -1
        );

        $this->assertEquals(3, count($keyboard->getProperty('inline_keyboard')));
    }

    public function testLinesMoreThanButtons()
    {
        $this->expectException(\PackBot\KeyboardException::class);
        $this->expectExceptionMessage('The number of lines is greater than the number of buttons.');

        new MultiRowInlineKeyboard(
            [
            [
                'text'          => '1',
                'callback_data' => '1',
            ],
            [
                'text'          => '2',
                'callback_data' => '2',
            ],
            [
                'text'          => '3',
                'callback_data' => '3',
            ],
        ],
            4
        );
    }

    public function testZeroLines()
    {
        $this->expectException(\PackBot\KeyboardException::class);
        $this->expectExceptionMessage('The number of lines must be greater than 0. If you want a single row, set 1 line or use InlineKeyboard instead.');

        new MultiRowInlineKeyboard(
            [
            [
                'text'          => '1',
                'callback_data' => '1',
            ],
            [
                'text'          => '2',
                'callback_data' => '2',
            ],
            [
                'text'          => '3',
                'callback_data' => '3',
            ],
            ],
            0
        );
    }
}
