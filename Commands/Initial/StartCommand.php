<?php
namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Conversation;
use PackBot\LanguageChoiseScreen;
use PackBot\MainMenuScreen;
use PackBot\UserSettings;

class StartCommand extends UserCommand
{
    protected $name         = 'start';
    protected $description  = 'Initial command. If the user has not yet selected a language,
                                then such a choice is offered first. After selecting a language or
                                if it has already been selected, the main menu opens';
    protected $usage        = '/start';
    protected $version      = '1.0.0';

    public function execute(): ServerResponse {

        /**
         * Clear any active conversation.
         */
        $conversation = new Conversation($this->getMessage()->getFrom()->getId(), $this->getMessage()->getChat()->getId(), $this->getName());
        $conversation->stop();


        /**
         * Set userID to global static variable.
         */
        UserSettings::setUserID($this->getMessage()->getFrom()->getId());

        $settings = new UserSettings();

        if (!$settings->isUserHasSettings()) {

            $screen = new LanguageChoiseScreen($this);
            return $screen->executeScreen();

        } else {

            $screen = new MainMenuScreen($this);
            return $screen->executeScreen();

        }
    }
}
