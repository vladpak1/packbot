<?php
/**
 * Callback query command
 *
 * This command handles all callback queries sent via inline keyboard buttons.
 *
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use PackBot\CallbackExecutor;
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
     * Main command execution
     *
     * @return ServerResponse
     * @throws \Exception
     */
    public function execute(): ServerResponse
    {

        /**
         * Set userID to global static variable.
         */
        UserSettings::setUserID($this->getCallbackQuery()->getFrom()->getId());

        $callbackExecutor = new CallbackExecutor($this);

        return $callbackExecutor->execute();
    }
}
