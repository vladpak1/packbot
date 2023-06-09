<?php

namespace PackBot;

use Longman\TelegramBot\Commands\SystemCommands\CallbackqueryCommand;
use Longman\TelegramBot\Entities\ServerResponse;

class CallbackExecutor
{
    protected CallbackqueryCommand $command;

    public function __construct($command)
    {
        $this->command = $command;
    }

    public function execute(): ServerResponse
    {
        /**
         * Callback data is a string with information about that callback.
         * First comes the name of the screen to which this callback belongs,
         * and then after "_" comes the callback value itself, which must be passed to the screen.
         */
        $callback    = $this->command->getCallbackQuery()->getData();
        $callback    = explode('_', $callback);
        $callback[0] = $callback[0] . 'Screen';
        $newMessage  = false;

        if (str_contains($callback[0], '{NEW}')) {
            $newMessage  = true;
            $callback[0] = str_replace('{NEW}', '', $callback[0]);
        }

        $callbackForCall = 'PackBot\\' . $callback[0];

        if (!class_exists($callbackForCall)) {
            throw new ScreenException('Callback class not found: ' . $callbackForCall);
        }

        $screen = new $callbackForCall($this->command);

        if ($newMessage) {
            $screen->blockSideExecute();
        }

        if (2 == count($callback)) {
            return $screen->executeCallback($callback[1]);
        } elseif (3 == count($callback)) {
            return $screen->executeCallbackWithAdditionalData($callback[1], $callback[2]);
        } elseif (4 == count($callback)) {
            return $screen->executeCallbackWithAdditionalData($callback[1], $callback[2], $callback[3]);
        } elseif (5 == count($callback)) {
            return $screen->executeCallbackWithAdditionalData($callback[1], $callback[2], $callback[3], $callback[4]);
        }

        throw new ScreenException('Callback data is invalid: ' . print_r($callback, true));

    }

    /**
     * Forcing directly pushed callback to be executed.
     *
     * @param string $callback The string just as is in telegram callback data.
     */
    public function forceCallback(string $callback): ServerResponse
    {
        $callback    = explode('_', $callback);
        $callback[0] = $callback[0] . 'Screen';
        $newMessage  = false;

        if (str_contains($callback[0], '{NEW}')) {
            $newMessage  = true;
            $callback[0] = str_replace('{NEW}', '', $callback[0]);
        }

        $callbackForCall = 'PackBot\\' . $callback[0];

        if (!class_exists($callbackForCall)) {
            throw new ScreenException('Callback class not found: ' . $callbackForCall);
        }

        $screen = new $callbackForCall($this->command);

        if ($newMessage) {
            $screen->blockSideExecute();
        }

        if (2 == count($callback)) {
            return $screen->executeCallback($callback[1]);
        } elseif (3 == count($callback)) {
            return $screen->executeCallbackWithAdditionalData($callback[1], $callback[2]);
        }

        throw new ScreenException('Callback data is invalid: ' . print_r($callback, true));

    }
}
