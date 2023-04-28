<?php

namespace Longman\TelegramBot\Commands\AdminCommands;

use Longman\TelegramBot\Commands\AdminCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use PackBot\AdminStatistics;

class BotStatsCommand extends AdminCommand {
    /**
     * @var string
     */
    protected $name = 'botstats';

    /**
     * @var string
     */
    protected $description = 'Show global bot stats.';

    /**
     * @var string
     */
    protected $usage = '/botstats';

    /**
     * @var string
     */
    protected $version = '1.0.0';

    /**
     * @var bool
     */
    protected $need_mysql = true;

    /**
     * Command execute method
     *
     * @return ServerResponse
     */
    public function execute(): ServerResponse {
        $message = $this->getMessage();

        $chat_id = $message->getChat()->getId();

        $stat = new AdminStatistics();
        
        $raw = print_r($stat->getRawData(), true);
        

        $data = [
            'chat_id' => $chat_id,
            'text'    => $raw,
            'disable_web_page_preview' => true,
        ];

        return Request::sendMessage($data);
    }
}
