<?php
namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use PackBot\Text;
use PackBot\UserSettings;

/**
 * Generic command
 */
class GenericCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'generic';

    /**
     * @var string
     */
    protected $description = 'Handles generic commands or is executed by default when a command is not found';

    /**
     * @var string
     */
    protected $version = '1.1.0';

    public function execute(): ServerResponse {

        /**
         * Set userID to global static variable.
         */
        UserSettings::setUserID($this->getMessage()->getFrom()->getId());

        $text = new Text();

        $this->replyToChat($this->getMessage()->getText(false));


        $genericMessage = $text->concatEOL(
            'Команда не найдена.',
            'Используйте /help для получения списка команд.'
        );

        return Request::sendMessage(array(
            'chat_id' => $this->getMessage()->getChat()->getId(),
            'text' => $genericMessage,
        ));
    }


}
