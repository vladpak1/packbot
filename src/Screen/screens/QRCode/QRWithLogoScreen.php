<?php

namespace PackBot;

use Longman\TelegramBot\Commands\Command;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use PackBot\QRCode\Generators\QRCodeWithLogoColored;

final class QRWithLogoScreen extends Screen implements ScreenInterface
{
    protected Command $command;

    protected Keyboard|InlineKeyboard $keyboard;

    protected Keyboard $afterKeyboard;

    protected Text $text;

    protected Conversation $conversation;

    protected string $screenName = 'QRWithLogo';

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
                    'Этот инструмент позволяет получить QR-код с логотипом.',
                    'Обратите внимание, что нужно отправлять логотип как файл (без сжатия).',
                    '',
                    '<b>Отправьте ссылку вместе с логотипом.</b>',
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

        $inputDomain = $this->getMessage()->getCaption();
        $document    = $this->getMessage()->getDocument() ?? false;

        if ($this->getMessage()->getPhoto()) {
            return $this->tryToSendMessage($this->text->e('Пожалуйста, отправьте логотип как файл в формате PNG, а не как фото.'), false);
        }

        if (count(explode(PHP_EOL, $inputDomain)) > 1) {
            return $this->tryToSendMessage($this->text->e('Этот инструмент может сгенерировать только один QR код за раз.'), false);
        }

        if (empty($inputDomain)) {
            return $this->tryToSendMessage($this->text->e('Вы не приложили логотип или отправили логотип без ссылки.'), false);
        }

        if (!Url::isValid($inputDomain)) {
            return $this->tryToSendMessage($this->text->e('Некорректный домен. Проверьте правильность ввода и повторите попытку.'), false);
        }

        if (!$document) {
            return $this->tryToSendMessage($this->text->e('Пожалуйста, отправьте логотип как файл в формате PNG.'), false);
        }

        if ('image/png' !== $document->getMimeType()) {
            return $this->tryToSendMessage($this->text->e('Ваш логотип не является PNG файлом.'), false);
        }

        try {
            $logoFile = TempFile::downloadFileFromTelegram($document->getFileId());
        } catch (\Exception $e) {
            error_log($e->getMessage());

            return $this->tryToSendMessage($this->text->e('Не удалось загрузить логотип. Попробуйте еще раз.'), false);
        }

        /**
         * Check MIME-Type.
         */
        if ('image/png' !== TempFile::getMimeType($logoFile)) {
            return $this->tryToSendMessage($this->text->e('Ваш логотип не является PNG файлом.'), false);
        }

        $conversation->stop();

        $response        = $this->tryToSendMessage($this->text->e('QR-код генерируется...'), false);
        $reportMessageID = $response->getResult()->getMessageId();

        try {
            $qrCode = new QRCodeWithLogoColored($inputDomain);
            $path   = $qrCode->setLogo($logoFile)->render()->toTemp();
        } catch (\Throwable $e) {
            return Request::editMessageText([
                'chat_id'      => $this->isSideExecute() ? $this->command->getCallbackQuery()->getFrom()->getId() : $this->command->getMessage()->getChat()->getId(),
                'message_id'   => $reportMessageID,
                'text'         => $this->text->e('Простите, но QR-код не может быть создан. Ошибка:') . ' ' . $e->getMessage(),
                'parse_mode'   => 'HTML',
                'reply_markup' => $this->afterKeyboard,
            ]);
        }

        Request::sendDocument([
            'chat_id'  => $this->getChatID(),
            'document' => Request::encodeFile($path),
            'caption'  => $this->text->sprintf('QR-код с логотипом для %s.', $inputDomain),
        ]);

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
