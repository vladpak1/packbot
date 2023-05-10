<?php

namespace PackBot;

use Longman\TelegramBot\Commands\Command;
use Longman\TelegramBot\Commands\SystemCommands\CallbackqueryCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use Throwable;

abstract class Screen implements ScreenInterface
{
    /**
     * A command or callback object that implements a screen.
     */
    protected Command $command;

    /**
     * A keyboard that will be sent with the message.
     */
    protected Keyboard|InlineKeyboard $keyboard;

    /**
     * A text object that will be used to send messages.
     */
    protected Text $text;

    /**
     * Determines if the context is a callback or a command.
     */
    protected bool $isCallback;

    /**
     * The Conversation object that is used to store data between requests.
     */
    protected Conversation $conversation;

    /**
     * This function is called by the CallbackExecutor on the callback associated with this screen.
     * It determines the type of callback and returns a response to it.
     */
    abstract public function executeCallback(string $callback): ServerResponse;

    /**
     * Called during execution as a normal command.
     */
    abstract public function executeScreen(): ServerResponse;

    /**
     * An abstract screen class that implements some common
     * methods and defines how the logic of screens should be built.
     */
    public function __construct(Command $command)
    {
        $this->command    = $command;
        $this->text       = new Text();
        $this->isCallback = $command instanceof CallbackqueryCommand;
    }

    /**
     * Set conversation.
     * Must be immutable.
     */
    public function setConversation(Conversation $conversation)
    {
        if (isset($this->conversation)) {
            throw new ScreenException('Conversation is already set.');
        }
        $this->conversation = $conversation;
    }

    /**
     * Sends a message to the user that an error has occurred.
     */
    final public function sendSomethingWrong(): ServerResponse
    {

        $message = $this->text->concatEOL(
            'Что-то пошло не так... Извините!',
            'Попробуйте еще раз или используйте команду /reload, чтобы перезапустить бота.',
            'Если проблема не исчезает, обратитесь к автору бота.'
        );

        if ($this->isCallback()) {
            return $this->command->getCallbackQuery()->answer([
                'text'       => $message,
                'show_alert' => true,
                'cache_time' => 5,
            ]);
        }

        return $this->tryToSendMessage($message);

    }

    /**
     * The function itself determines if we are trying to change the screen not by creating a new message,
     * but by replacing the previous one and changes its behavior based on this fact.
     *
     * Do not forget that the text is translated inside this function and does not need to be translated and escaped first.
     *
     * @param  string|array            $text           Required. Text of the message to be sent. Note that the text is escaped and tranlated inside the function.
     * @param  InlineKeyboard|Keyboard $reply_markup   Optional. Keyboard or inline keyboard that will be sent with the message. If false, the keyboard will be removed.
     * @param  bool                    $useTranslate   Optional. If true, the text will be translated. If false, the text will be sent as is.
     * @param  array                   $additionalData Optional. Additional data that will be sent with the message to Telegram.
     * @return ServerResponse          A response from the Telegram server.
     */
    final public function maybeSideExecute(string|array $text, Keyboard $reply_markup, bool $useTranslate = true, array $additionalData = []): ServerResponse
    {

        if ($useTranslate) {
            $text = is_array($text) ? $this->text->concatEOL(...$text) : $this->text->e($text);
        } else {
            $text = is_array($text) ? implode(PHP_EOL, ...$text) : $text;
        }

        $mergedData = array_merge([
            'text'         => $text,
            'reply_markup' => $reply_markup,
        ], $additionalData);

        if ($this->isSideExecute()) {
            $chat_id    = $this->command->getCallbackQuery()->getFrom()->getId();
            $message_id = $this->command->getCallbackQuery()->getRawData()['message']['message_id'];

            return Request::editMessageText(array_merge([
                'chat_id'    => $chat_id,
                'message_id' => $message_id,
            ], $mergedData));
        }

        $message = $this->command->getMessage();
        $chat_id = $message->getChat()->getId();

        return Request::sendMessage(array_merge([
            'chat_id' => $chat_id,
        ], $mergedData));
    }

    /**
     * Tries to send a message to the user.
     * Since sometimes we respond to a callback, slightly different logic is required.
     *
     * @param string                        Required. Text of the message to be sent. Note that the text is NOT escaped and tranlated inside the function.
     * @param InlineKeyboard|Keyboard|false Optional. Keyboard or inline keyboard that will be sent with the message. If false, the keyboard will be removed.
     * @param array $additionalData Optional. Additional data that will be sent with the message to Telegram.
     */
    final protected function tryToSendMessage(string $text, InlineKeyboard|Keyboard|false $reply_markup = false, array $additionalData = []): ServerResponse
    {

        $chat_id = $this->isSideExecute() ? $this->command->getCallbackQuery()->getFrom()->getId() : $this->command->getMessage()->getChat()->getId();

        $passedData = [
            'chat_id' => $chat_id,
            'text'    => $text,
        ];

        if (false !== $reply_markup) {
            array_merge($passedData, ['reply_markup' => $reply_markup]);
        }

        return Request::sendMessage(array_merge($passedData, $additionalData));
    }

    /**
     * Getting message from the user.
     */
    final protected function getMessage(): Message
    {
        return $this->command->getMessage();
    }

    /**
     * Get text of the message.
     */
    final protected function getText(): string
    {
        return $this->getMessage()->getText();
    }

    /**
     * Used when for some reason you need to remember the id of the last message.
     *
     * @param ServerResponse $response A response from the Telegram server.
     */
    final protected function memberLastMessage(ServerResponse $response): void
    {
        if (false == $response->isOk()) {
            return;
        }

        $result     = $response->getResult();
        $chat_id    = $result->getChat()->getId();
        $message_id = $result->getMessageId();

        PackDB::memberLastMessageIDForChat($chat_id, $message_id);
    }

    /**
     * Used when you want to delete the last memorized message.
     * Note that calling a callback is also considered a message, so it's worth testing this feature thoroughly.
     *
     * @param int $minusIncrement Optional. If you need to delete a message that is not the last one, you can specify the number of messages to skip.
     */
    final protected function removeLastMessage($minusIncrement = 0): ServerResponse
    {
        $chat_id    = $this->getChatID();
        $message_id = PackDB::getLastMessageIDByChatID($chat_id);

        if (null == $message_id) {
            return Request::emptyResponse();
        }
        $message_id -= $minusIncrement;

        return Request::deleteMessage([
            'chat_id'    => $chat_id,
            'message_id' => $message_id,
        ]);
    }

    /**
     * Getting the chatID. If the context is a callback, then the chatID is taken from the callback.
     */
    final protected function getChatID(): int
    {
        if ($this->isSideExecute()) {
            return $this->command->getCallbackQuery()->getFrom()->getId();
        }

        return $this->command->getMessage()->getChat()->getId();
    }

    final protected function getUserID(): int
    {
        if ($this->isSideExecute()) {
            return $this->command->getCallbackQuery()->getFrom()->getId();
        }

        return $this->command->getMessage()->getFrom()->getId();
    }

    /**
     * We call side execution a context in which a screen change occurs in response to a callback,
     * and not by sending a message.
     * That is, the message is being edited, not a new one is being sent.
     *
     * @return bool Whether the screen is being changed in response to a callback.
     */
    final protected function isSideExecute(): bool
    {
        try {
            $this->command->getMessage()->getChat()->getId();

            return false;
        } catch (Throwable $e) {
            return true;
        }
    }

    final protected function isCallback()
    {
        return $this->isCallback;
    }

    /**
     * Tries to send a temporary message to the user.
     * If the message is sent successfully, it will be deleted after the specified time.
     * You can disable such messages in the config.
     *
     * @param string Required. Text of the message to be sent. Note that the text is escaped and tranlated inside the function.
     * @param int    Optional. Time in seconds after which the message will be deleted. The maximum value is 30 seconds.
     * @return bool Whether the message was sent successfully.
     */
    final protected function tryToSendTempMessage(string $text, int $timeout = 10): bool
    {

        if (!Environment::var('screen_settings')['useTempMessages']) {
            return false;
        }

        $result = $this->tryToSendMessage($text, false);

        if ($result->isOk()) {
            $messageID = $result->getResult()->getMessageId();

            /**
             * If timeout too big, decrease it to 30 seconds.
             */
            if ($timeout > 30) {
                $timeout = 30;
            }

            sleep($timeout);
            Request::deleteMessage([
                'chat_id'    => $this->getChatID(),
                'message_id' => $messageID,
            ]);

            return true;
        }

        return false;
    }

    protected function loadConversation()
    {
        if (!isset($this->conversation)) {
            $this->conversation = new Conversation($this->getChatID(), $this->getUserID(), 'conversationHandler');
        }
    }
}
