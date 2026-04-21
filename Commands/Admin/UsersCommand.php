<?php

namespace Longman\TelegramBot\Commands\AdminCommands;

use Longman\TelegramBot\Commands\AdminCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use PackBot\PackDB;

class UsersCommand extends AdminCommand
{
    /**
     * @var string
     */
    protected $name = 'users';

    /**
     * @var string
     */
    protected $description = 'Echoes a total number of users and the amount of new users for today';

    /**
     * @var string
     */
    protected $usage = '/users';

    /**
     * @var string
     */
    protected $version = '1.0.0';

    /**
     * @var bool
     */
    protected $need_mysql = true;

    /**
     * Command execute method.
     *
     */
    public function execute(): ServerResponse
    {
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();

        $users = PackDB::getUsersIDs();

        $this->replyToChat('Total users: ' . count($users), [
            'parse_mode' => 'HTML',
        ]);

        $this->replyToChat('New users today: ' . count(PackDB::getNewUsersIDs()), [
            'parse_mode' => 'HTML',
        ]);

        return Request::emptyResponse();
    }
}
