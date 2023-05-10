<?php
/**
 * Callback query command.
 *
 * This command handles all callback queries sent via inline keyboard buttons.
 *
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use PackBot\CallbackExecutor;
use PackBot\Text;
use PackBot\UserSettings;

class CallbackqueryCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'callbackquery';

    /**
     * @var string
     */
    protected $description = 'Handle the callback query';

    /**
     * @var string
     */
    protected $version = '1.0.0';

    /**
     * Main command execution.
     *
     * @throws \Exception
     */
    public function execute(): ServerResponse
    {

        /**
         * Set userID to global static variable.
         */
        UserSettings::setUserID($this->getCallbackQuery()->getFrom()->getId());

        try {

            $callbackExecutor = new CallbackExecutor($this);

            return $callbackExecutor->execute();

        } catch (\Throwable $th) {
            $text = new Text();
            error_log($th->getMessage() . PHP_EOL . $th->getTraceAsString());

            return Request::sendMessage([
                'chat_id' => $this->getCallbackQuery()->getMessage()->getChat()->getId(),
                'text'    => $text->e('Что-то пошло не так. Попробуйте еще раз или используйте /reload, чтобы перезагрузить бота.'),
            ]);
        }
    }
}
