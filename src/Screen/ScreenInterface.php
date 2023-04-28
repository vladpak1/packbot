<?php
namespace PackBot;

use Longman\TelegramBot\Commands\Command;
use Longman\TelegramBot\Commands\SystemCommands\CallbackqueryCommand;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;

interface ScreenInterface {

    /**
     * The screen receives the context from UserCommand of CallbackqueryCommand.
     * Class instance must be immutable
     * 
     * @param UserCommand|CallbackqueryCommand Required. A command or callback object that implements a screen.
     */
    public function __construct(Command $command);

    /**
     * This method is called by the CallbackExecutor on the callback associated with this screen.
     * It determines the type of callback and returns a response to it.
     */
    public function executeCallback(string $callback): ServerResponse;

    /**
     * Called during execution as a normal command.
     */
    public function executeScreen(): ServerResponse;
}
