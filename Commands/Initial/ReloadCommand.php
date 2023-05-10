<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;

class ReloadCommand extends UserCommand
{
    protected $name = 'reload';

    protected $description = 'Reload command.';

    protected $usage = '/reload';

    protected $version = '1.0.0';

    public function execute(): ServerResponse
    {
        return $telegram = $this->getTelegram()->executeCommand('start');
    }
}
