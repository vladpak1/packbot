<?php

namespace Longman\TelegramBot\Commands\AdminCommands;

use Longman\TelegramBot\Commands\AdminCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use PackBot\PackDB;

class UsersListCommand extends AdminCommand
{
    /**
     * @var string
     */
    protected $name = 'userslist';

    /**
     * @var string
     */
    protected $description = 'Send a txt file with list of users ids';

    /**
     * @var string
     */
    protected $usage = '/userslist';

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

        $tempFile = tempnam(sys_get_temp_dir(), 'userslist') . '.txt';

        $content = implode(PHP_EOL, $users);

        file_put_contents($tempFile, $content);

        // Send the file
        Request::sendDocument([
            'chat_id'                  => $chat_id,
            'document'                 => Request::encodeFile($tempFile),
            'caption'                  => 'Пользователя',
            'disable_web_page_preview' => true,
        ]);

        return Request::emptyResponse();
    }
}
