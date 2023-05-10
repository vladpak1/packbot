<?php
/**
 * Callback query command.
 *
 * This command handles all callback queries sent via inline keyboard buttons.
 *
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use PackBot\UserSettings;

class ConversationHandlerCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'conversationHandler';

    /**
     * @var string
     */
    protected $description = 'Handle the conversation';

    /**
     * @var string
     */
    protected $version = '1.2.0';

    /**
     * Main command execution.
     *
     * @throws \Exception
     */
    public function execute(): ServerResponse
    {
        $message = $this->getMessage();
        $chat    = $message->getChat();
        $user    = $message->getFrom();
        $text    = trim($message->getText(true));
        $chat_id = $chat->getId();
        $user_id = $user->getId();

        /**
         * Set userID to global static variable.
         */
        UserSettings::setUserID($user_id);

        $conversation = new Conversation($user_id, $chat_id, $this->getName());

        $notes = &$conversation->notes;

        !is_array($notes) && $notes = [];

        if (isset($notes['screenName'])) {
            $screenName = $notes['screenName'];

            $screenName = 'PackBot\\' . $screenName . 'Screen';

            $screen = new $screenName($this);
            $screen->setConversation($conversation);
            $screen->executeScreen();
        } else {
            error_log('ConversationHandlerCommand: no screenName in notes');
        }

        return Request::emptyResponse();
    }
}
