<?php

namespace PackBot;

use Longman\TelegramBot\Commands\Command;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use PackBot\QRCode\Generators\QRCode;

final class QRSimpleScreen extends Screen implements ScreenInterface
{
    protected Command $command;

    protected Keyboard|InlineKeyboard $keyboard;

    protected Keyboard $afterKeyboard;

    protected Text $text;

    protected Conversation $conversation;

    protected string $screenName = 'QRSimple';

    public function __construct(Command $command)
    {
        parent::__construct($command);
        $this->prepareKeyboard();
        $this->prepareAfterKeyboard();
        $this->command = $command;
        $this->text    = new Text();
    }

    public function executeScreen(): ServerResponse
    {
        return $this->startConversation();
    }

    public function executeCallback(string $callback): ServerResponse
    {

        $this->loadConversation();
        $this->conversation->stop();

        switch($callback) {
            default:
                error_log('An attempt to execute undefined callback for screen ' . $this->screenName . ': ' . $callback);

                return $this->sendSomethingWrong();
            case 'back':
                $screen = new QRCodeScreen($this->command);
                $screen->executeScreen();

                return $this->command->getCallbackQuery()->answer();
            case 'anotherCheck':
                $screen = new self($this->command);
                $screen->executeScreen();

                return $this->command->getCallbackQuery()->answer();
            case 'saveReport':
                $screen = new MainMenuScreen($this->command);
                $screen
                ->blockSideExecute()
                ->executeScreen();

                return $this->command->getCallbackQuery()->answer();
        }
    }

    protected function startConversation()
    {

        if (!isset($this->conversation)) {
            $this->conversation = new Conversation($this->getChatID(), $this->getUserID(), 'conversationHandler');
        }

        $conversation = &$this->conversation;
        $notes        = &$conversation->notes;
        !is_array($notes) && $notes
                          = [];

        $state               = $notes['state'] ?? 0;
        $notes['screenName'] = $this->screenName;

        //State machine
        switch ($state) {
            case 0:
                $notes['state'] = 1;
                $conversation->update();

                return $this->maybeSideExecute([
                    'Этот инструмент позволяет получить QR-код для ссылки.',
                    '',
                    '<b>Отправьте ссылку или список ссылок (до 5).</b>',
                    '',
                ], $this->keyboard, true, [
                    'parse_mode' => 'HTML',
                ]);
            case 1:
                return $this->proccessCheckRequest($conversation);
            default:
                return $this->sendSomethingWrong();
        }
    }

    protected function forceReplyKeyboard()
    {
        return Keyboard::forceReply();
    }

    protected function prepareKeyboard()
    {

        $this->keyboard = new InlineKeyboard([
            [
                'text'          => $this->text->e('Назад ⬅️'),
                'callback_data' => $this->screenName . '_back',
            ],
        ]);
    }

    protected function prepareAfterKeyboard()
    {
        $this->afterKeyboard = new MultiRowInlineKeyboard([
            [
                'text'          => $this->text->e('Еще одна проверка'),
                'callback_data' => $this->screenName . '_anotherCheck',
            ],
            [
                'text'          => $this->text->e('Назад ⬅️'),
                'callback_data' => $this->screenName . '_back',
            ],
        ], 2);
    }

    private function proccessCheckRequest($conversation)
    {
        $inputDomains = array_unique(explode(PHP_EOL, $this->getText()));

        foreach ($inputDomains as $key => $domain) {
            $inputDomains[$key] = mb_strtolower(trim($domain));

            if (!Url::isValid($domain)) {
                return $this->tryToSendMessage(
                    $this->text->sprintf('Ссылка не является корректной: %s. Исправьте ошибку и попробуйте снова.', $domain),
                    false
                );
            }
        }

        if (count($inputDomains) > 5) {
            return $this->tryToSendMessage(
                $this->text->e('Слишком много ссылок. Отправьте не более 5 ссылок. Исправьте ошибку и попробуйте снова.'),
                false
            );
        }

        $conversation->stop();

        $response        = $this->tryToSendMessage($this->text->e('QR-коды генерируется...'), false);
        $reportMessageID = $response->getResult()->getMessageId();

        try {
            foreach ($inputDomains as $domain) {
                $qrCode = new QRCode($domain);
                $path   = $qrCode->render()->toTemp();

                Request::sendDocument([
                    'chat_id'                  => $this->getChatID(),
                    'document'                 => Request::encodeFile($path),
                    'caption'                  => $this->text->sprintf('QR-код для %s.', $domain),
                    'disable_web_page_preview' => true,
                ]);
            }
        } catch (\Throwable $e) {
            return Request::editMessageText([
                'chat_id'      => $this->isSideExecute() ? $this->command->getCallbackQuery()->getFrom()->getId() : $this->command->getMessage()->getChat()->getId(),
                'message_id'   => $reportMessageID,
                'text'         => $this->text->e('Простите, но QR-код не может быть создан. Ошибка:') . ' ' . $e->getMessage(),
                'parse_mode'   => 'HTML',
                'reply_markup' => $this->afterKeyboard,
            ]);
        }

        Request::deleteMessage([
            'chat_id'    => $this->getChatID(),
            'message_id' => $reportMessageID,
        ]);

        return Request::sendMessage([
            'chat_id'                  => $this->isSideExecute() ? $this->command->getCallbackQuery()->getFrom()->getId() : $this->command->getMessage()->getChat()->getId(),
            'text'                     => $this->text->e('Результат отправлен как файл.'),
            'reply_markup'             => $this->afterKeyboard,
            'disable_web_page_preview' => true,
        ]);

        $this->tryToSendTempMessage('Один из отчетов готов! Посмотрите в чат.', 2);

    }
}
