<?php

namespace PackBot\Tests\Functional;

use GuzzleHttp\Psr7\Response;
use Longman\TelegramBot\Entities\Update;

/**
 * Base class for function command tests.
 */
abstract class CommandTestCase extends TestWithEnvCase {

    protected function generateUpdateForCommand(string $command): Update {
        return new Update([
            'update_id' => mt_rand(1, 1000),
            'message'   => [
                'message_id' => 1,
                'from'       => [
                    'id'         => 123456789,
                    'is_bot'     => false,
                    'first_name' => 'Test',
                    'last_name'  => 'User',
                    'username'   => 'testuser',
                ],
                'chat'       => [
                    'id'         => mt_rand(1, 1000),
                    'first_name' => 'Test',
                    'last_name'  => 'User',
                    'username'   => 'testuser',
                    'type'       => 'private',
                ],
                'date'       => time(),
                'text'       => $command,
            ],
        ]);
    }

    protected function generateSuccessfulTelegramResponse(Update $update): Response {
        return new Response(200, array(), $update->toJson());
    }

    protected function decodeJsonEncodedString(string $json): string {
        $result = json_decode('"' . $json . '"');
        if ($result === null) $this->fail('JSON decode failed: ' . json_last_error_msg());

        return $result;
    }

    protected function getInlineKeyboardsButtonsByJson(string $reply_markup): array {
        $keyboard = json_decode($reply_markup, true);
        if (!isset($keyboard['inline_keyboard'])) $this->fail('InlineKeyboard is not set');
        
        $buttons = array();

        foreach ($keyboard['inline_keyboard'] as $row) {
            foreach ($row as $button) {
                $buttons[] = $button;
            }
        }
        
        return $buttons;
    }

    protected function getFirstButtonOfInlineKeyboard(string $reply_markup) {
        $buttons = $this->getInlineKeyboardsButtonsByJson($reply_markup);
        return $buttons[0];
    }
}
