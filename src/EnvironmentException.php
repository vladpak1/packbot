<?php

namespace PackBot;

class EnvironmentException extends \Exception
{
    public function __construct(string $message = '', int $code = 0, \Throwable $previous = null)
    {
        $messageAddon = 'Apparently, something is wrong with your config. Please check that the settings are correct. Everything you need is in the documentation. <br>Error: ';
        parent::__construct($messageAddon . $message, $code, $previous);
    }
}
