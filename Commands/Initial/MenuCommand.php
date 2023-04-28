<?php
namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use PackBot\MainMenuScreen;
use PackBot\UserSettings;

class MenuCommand extends UserCommand
{
    protected $name         = 'menu';
    protected $description  = 'Main menu command.';
    protected $usage        = '/menu';
    protected $version      = '1.0.0';

    public function execute(): ServerResponse {

        /**
         * Set userID to global static variable.
         */
        UserSettings::setUserID($this->getMessage()->getFrom()->getId());

        $screen = new MainMenuScreen($this);
        return $screen->executeScreen();
    }
}
