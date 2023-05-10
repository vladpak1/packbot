<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use PackBot\CatsDB;
use PackBot\Text;
use PackBot\UserSettings;

class CatReloadCommand extends UserCommand
{
    protected $name = 'cat';

    protected $description = 'Use this command to reload cat history.';

    protected $usage = '/catreload';

    protected $version = '1.0.0';

    public function execute(): ServerResponse
    {

        /**
         * Set userID to global static variable.
         */
        UserSettings::setUserID($this->getMessage()->getFrom()->getId());

        $text   = new Text();
        $chatID = $this->getMessage()->getChat()->getId();

        CatsDB::clearSeen($this->getMessage()->getFrom()->getId());

        return Request::sendMessage([
            'chat_id' => $chatID,
            'text'    => $text->e('История очищена! Используйте /cat, чтобы получить кота. 🐱'),
        ]);
    }
}
