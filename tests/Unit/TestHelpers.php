<?php

namespace PackBot\Tests\Unit;

use Longman\TelegramBot\Entities\Update;

class TestHelpers {

    /**
     * Returns the array with test elements in ScrollableKeyboard format.
     * 
     * @param int $count The number of elements to be returned.
     */
    public static function getTestItems(int $count): array {
        $testItems = array();
        for ($i = 0; $i < $count; $i++) {
            $testItems[] = array(
                'text' => 'Test item ' . ($i + 1) . '/' . $count,
                'id'   => $i + 1
            );
        }
        return $testItems;
    }

    /**
     * Return a simple fake Update object
     *
     * @param array $data Pass custom data array if needed
     *
     * @return Update
     */
    public static function getFakeUpdateObject(array $data = []): Update {
        $data = $data ?: [
            'update_id' => mt_rand(),
            'message'   => [
                'message_id' => mt_rand(),
                'chat'       => [
                    'id' => mt_rand(),
                ],
                'date'       => time(),
            ],
        ];
        return new Update($data, 'testbot');
    }


}