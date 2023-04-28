<?php
namespace PackBot;

use Longman\TelegramBot\Entities\InlineKeyboard;

/**
 * TODO: RENAMT TO InlineKeyboardUtils
 */
class inlineKeyboardSeparator {
    
    /**
     * if rows = 0, then calculate automatically
     */
    public static function rowsOfButtons(array $arr, int $divisions = 2): array {

        $total_count = count($arr);
        $elements_per_division = ceil($total_count / $divisions); // round up to ensure all divisions have at least 1 element

        $divided_arr = array_chunk($arr, $elements_per_division); // divide array into chunks of desired size
        Debug::toConsoleAndExit($divided_arr);
        return $divided_arr;




    }

    public static function createInlineKeyboard($buttons, $lines) {
        // Check if the number of lines is valid
        if ($lines < 1) {
            return null;
        }
        
        // Calculate the number of buttons per line
        $total = count($buttons);
        $per_line = ceil($total / $lines);
        
        // Split the buttons array into chunks
        $chunks = array_chunk($buttons, $per_line);
        
        // Create a new inline keyboard with the chunks
        $keyboard = new InlineKeyboard(...$chunks);
        
        // Return the keyboard
        return $keyboard;
    }
    
}