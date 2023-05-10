<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use PackBot\LanguageChoiseScreen;
use PackBot\UserSettings;

class LanguageCommand extends UserCommand
{
    protected $name = 'language';

    protected $description = 'Language menu command.';

    protected $usage = '/language';

    protected $version = '1.0.0';

    public function execute(): ServerResponse
    {

        /**
         * Set userID to global static variable.
         */
        UserSettings::setUserID($this->getMessage()->getFrom()->getId());

        $screen = new LanguageChoiseScreen($this);

        return $screen->executeScreen();
    }
}
