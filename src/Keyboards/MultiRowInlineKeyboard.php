<?php

namespace PackBot;

use Longman\TelegramBot\Entities\InlineKeyboard;

class MultiRowInlineKeyboard extends InlineKeyboard
{
    private array $buttons;

    private int $lines;

    /**
     * Creates a multi-row inline keyboard.
     * Usage: pass an array with the given buttons and the number of lines.
     *
     * If you want to choose line for each button, you can pass "line" key to the button array. Note that it will override the number of lines.
     *
     * @param  array             $buttons The buttons to be added to the keyboard.
     *                                    )
     * @param  int               $lines   The number of lines. Default is 2. If you want a single row, set 1 line or use InlineKeyboard instead. If you want to have as many lines as buttons, set -1.
     * @throws KeyboardException
     */
    public function __construct(array $buttons, int $lines = 2)
    {
        $this->buttons = $buttons;
        $this->lines   = $lines;

        if (-1 == $this->lines) {
            $this->lines = count($this->buttons);
        }

        if ($this->lines < 1) {
            throw new KeyboardException('The number of lines must be greater than 0. If you want a single row, set 1 line or use InlineKeyboard instead.');
        }

        if ($this->lines > count($this->buttons)) {
            throw new KeyboardException('The number of lines is greater than the number of buttons.');
        }

        $total   = count($this->buttons);
        $perLine = ceil($total / $this->lines);
        $chunks  = array_chunk($this->buttons, $perLine);

        parent::__construct(...$chunks);

    }
}
