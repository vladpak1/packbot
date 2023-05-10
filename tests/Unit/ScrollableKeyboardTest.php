<?php

namespace PackBot\Tests\Unit;

use PackBot\ScrollableKeyboard;
use PHPUnit\Framework\TestCase;

class ScrollableKeyboardTest extends TestCase
{
    public function testHasNextPages()
    {
        $testItems = TestHelpers::getTestItems(10);
        $keyboard  = (new ScrollableKeyboard())
        ->addEntries($testItems)
        ->setPerScreen(count($testItems));

        $this->assertFalse($keyboard->hasNextPage());
    }

    public function testHasNextPagesWithMoreItems()
    {
        $testItems = TestHelpers::getTestItems(10);
        $keyboard  = (new ScrollableKeyboard())
        ->addEntries($testItems)
        ->setPerScreen(count($testItems) - 1);

        $this->assertTrue($keyboard->hasNextPage());
    }

    public function testHasPreviousPages()
    {
        $testItems = TestHelpers::getTestItems(10);
        $keyboard  = (new ScrollableKeyboard())
        ->addEntries($testItems)
        ->setPerScreen(count($testItems));

        $this->assertFalse($keyboard->hasPreviousPage());
    }

    public function testIteratingThroughPages()
    {
        $testItems = TestHelpers::getTestItems(15);
        $keyboard  = (new ScrollableKeyboard())
        ->addEntries($testItems)
        ->setPerScreen(5) //should be 3 pages
        ->setCurrentPage(2);

        $this->assertTrue($keyboard->hasNextPage());
        $this->assertTrue($keyboard->hasPreviousPage());

        $keyboard->nextPage();
        $this->assertFalse($keyboard->hasNextPage());

        $keyboard->previousPage()->previousPage();
        $this->assertFalse($keyboard->hasPreviousPage());
    }

    public function testConstructionKeyboard()
    {
        $testItems    = TestHelpers::getTestItems(15);
        $screenItemID = random_int(1, 1000);
        $keyboard     = (new ScrollableKeyboard())
        ->addEntries($testItems)
        ->setPerScreen(5) //should be 3 pages
        ->setScreenItemID($screenItemID)
        ->setKeyboardScreen('testScreen')
        ->setCurrentPage(2);
        $raw = $keyboard->getKeyboard()->getRawData();

        $this->assertEquals(7, count($raw['inline_keyboard']));

        /**
         * @var Longman\TelegramBot\Entities\InlineKeyboardButton
         */
        $firstButton = $raw['inline_keyboard'][0][0];
        $this->assertEquals('testScreen_listItem_6', $firstButton->getCallbackData());

        /**
         * @var Longman\TelegramBot\Entities\InlineKeyboardButton
         */
        $lastButton = $raw['inline_keyboard'][5][2];
        $this->assertEquals("testScreen_listAction_{$screenItemID}_nextPage_2", $lastButton->getCallbackData());
    }

    public function testEmptyKeyboard()
    {
        $this->expectException(\PackBot\KeyboardException::class);
        $keyboard = (new ScrollableKeyboard())->getKeyboard();
    }
}
