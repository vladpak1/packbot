<?php

namespace PackBot;

class Debug
{
    public static function toConsoleAndExit($data)
    {
        error_log(print_r($data, true));
        exit;
    }

    public static function toConsole($data)
    {
        error_log(print_r($data, true));
    }

    public static function toConsoleConcat(...$data)
    {
        foreach ($data as $item) {
            error_log(print_r($item, true));
        }
    }
}
