<?php

namespace PackBot\Tests\Unit;

use PackBot\Time;
use PHPUnit\Framework\TestCase;
use PackBot\Text;

class TimeTest extends TestCase
{
    public function testTimeRussian()
    {
        /**
         * @var \PHPUnit\Framework\MockObject\MockObject|Text $textMock
         */
        $textMock = $this->getMockBuilder(Text::class)->getMock();
        $textMock
        ->method('getCurrentLanguage')
        ->willReturn('ru_RU');

        $time = new Time($textMock);

        $this->assertEquals('10 секунд', $time->secondsToHumanReadable(10));
        $this->assertEquals('1 минута', $time->secondsToHumanReadable(60));
        $this->assertEquals('1 минута 10 секунд', $time->secondsToHumanReadable(70));
        $this->assertEquals('1 час', $time->secondsToHumanReadable(3650));
        $this->assertEquals('1 час 1 минута', $time->secondsToHumanReadable(3660));
        $this->assertEquals('2 часа 2 минуты', $time->secondsToHumanReadable(7320));
        $this->assertEquals('1 день 4 часа', $time->secondsToHumanReadable(100800));
        $this->assertEquals('3 дня 6 часов', $time->secondsToHumanReadable(280800));
    }

    public function testTimeEnglish()
    {
        /**
         * @var \PHPUnit\Framework\MockObject\MockObject|Text $textMock
         */
        $textMock = $this->getMockBuilder(Text::class)->getMock();
        $textMock
        ->method('getCurrentLanguage')
        ->willReturn('en_US');

        $time = new Time($textMock);

        $this->assertEquals('10 seconds', $time->secondsToHumanReadable(10));
        $this->assertEquals('1 minute', $time->secondsToHumanReadable(60));
        $this->assertEquals('1 minute 10 seconds', $time->secondsToHumanReadable(70));
        $this->assertEquals('1 hour', $time->secondsToHumanReadable(3650));
        $this->assertEquals('1 hour 1 minute', $time->secondsToHumanReadable(3660));
        $this->assertEquals('2 hours 2 minutes', $time->secondsToHumanReadable(7320));
        $this->assertEquals('1 day 4 hours', $time->secondsToHumanReadable(100800));
        $this->assertEquals('3 days 6 hours', $time->secondsToHumanReadable(280800));
    }
}
