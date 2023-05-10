<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use PackBot\ListSitesScreen;
use PackBot\UserSettings;

class ListCommand extends UserCommand
{
    protected $name = 'list';

    protected $description = 'List sites command.';

    protected $usage = '/list';

    protected $version = '1.0.0';

    public function execute(): ServerResponse
    {

        /**
         * Set userID to global static variable.
         */
        UserSettings::setUserID($this->getMessage()->getFrom()->getId());

        $screen = new ListSitesScreen($this);

        return $screen->executeScreen();
    }
}
