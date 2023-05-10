<?php

namespace PackBot;

use Longman\TelegramBot\Commands\Command;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;

final class UrlTrimmerScreen extends Screen implements ScreenInterface
{
    protected Command $command;

    protected Keyboard|InlineKeyboard $keyboard;

    protected Keyboard $afterKeyboard;

    protected Text $text;

    protected Conversation $conversation;

    protected string $screenName = 'UrlTrimmer';

    protected int $maxUrlsAtOnce = 0;

    public function __construct(Command $command)
    {
        parent::__construct($command);
        $this->prepareKeyboard();
        $this->prepareAfterKeyboard();
        $this->command       = $command;
        $this->text          = new Text();
        $this->maxUrlsAtOnce = Environment::var('tools_settings')['UrlTrimmerTool']['maxUrlsAtOnce'];
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
                $screen = new ScriptsScreen($this->command);
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
                    'Этот инструмент позволяет очистить список ссылок от пробелов, некорректных ссылок и повторений.',
                    'Результат будет отправлен как текстовый файл, если ссылок много.',
                    $this->text->sprintf('Максимальное количество доменов для проверки: %d', $this->maxUrlsAtOnce),
                    '',
                    'Каждый домен с новой строки.',
                    '<b>Отправьте домен или список доменов (можно как txt файл)</b>',
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
                'text'          => $this->text->e('Перезапустить'),
                'callback_data' => $this->screenName . '_anotherCheck',
            ],
            [
                'text'          => $this->text->e('Сохранить этот отчет'),
                'callback_data' => $this->screenName . '_saveReport',
            ],
            [
                'text'          => $this->text->e('Назад ⬅️'),
                'callback_data' => $this->screenName . '_back',
            ],
        ], 2);
    }

    private function proccessCheckRequest($conversation)
    {
        /**
         * This script supports both text message and text file input.
         */

        /**
         * @var $document \Longman\TelegramBot\Entities\Document|false
         */
        $document = $this->getMessage()->getDocument() ?? false;

        if (!$document) {
            $message      = $this->getText();
            $inputDomains = explode(PHP_EOL, $message);
        }

        //if there's a file, we'll use it
        if ($document) {
            if (false !== $document && 'text/plain' != $document->getMimeType() || $document->getFileSize() > 1e+7) {
                return $this->tryToSendMessage($this->text->concatEOL(
                    'С вашим файлом что-то не так. Он должен иметь расширение .txt и весить не более 10 мб.',
                    'Попробуйте еще раз.',
                ));
            }

            try {
                $inputDomains = explode(PHP_EOL, TempFile::getTextFromFileFromTelegram($document->getFileId()));
            } catch (\Throwable $e) {
                error_log($e->getMessage());

                return $this->tryToSendMessage($this->text->concatEOL(
                    'С вашим файлом что-то не так. Он должен иметь расширение .txt и весить не более 10 мб.',
                    'Попробуйте еще раз.',
                ));
            }
        }

        if (count($inputDomains) > $this->maxUrlsAtOnce) {
            return $this->tryToSendMessage($this->text->concatEOL(
                $this->text->sprintf('Максимальное количество доменов для проверки: %d. Вы отправили %d доменов.', $this->maxUrlsAtOnce, count($inputDomains)),
                'Сократите количество доменов и попробуйте еще раз.',
            ));
        }

        $conversation->stop();

        $resultArray = [];

        foreach ($inputDomains as $domain) {
            $domain = urldecode(trim($domain));

            if (!Url::isValid($domain)) {
                continue;
            }

            $resultArray[] = $domain;
        }
        $resultArray = array_unique($resultArray);

        if (count($resultArray) > 5) {
            Request::sendDocument([
                'chat_id'  => $this->isSideExecute() ? $this->command->getCallbackQuery()->getFrom()->getId() : $this->command->getMessage()->getChat()->getId(),
                'document' => Request::encodeFile(TempFile::txt(implode(PHP_EOL, $resultArray), 'list')),
                'caption'  => $this->text->sprintf('Список доменов (%d), удалено %d доменов.', count($resultArray), count($inputDomains) - count($resultArray)),
            ]);

            return Request::sendMessage([
                'chat_id'                  => $this->isSideExecute() ? $this->command->getCallbackQuery()->getFrom()->getId() : $this->command->getMessage()->getChat()->getId(),
                'text'                     => $this->text->e('Результат отправлен как файл.'),
                'reply_markup'             => $this->afterKeyboard,
                'disable_web_page_preview' => true,
            ]);
        } elseif (0 == count($resultArray)) {
            return Request::sendMessage([
                'chat_id'                  => $this->isSideExecute() ? $this->command->getCallbackQuery()->getFrom()->getId() : $this->command->getMessage()->getChat()->getId(),
                'text'                     => $this->text->e('В списке не осталось доменов.'),
                'reply_markup'             => $this->afterKeyboard,
                'disable_web_page_preview' => true,
            ]);
        }

        return Request::sendMessage([
            'chat_id'                  => $this->isSideExecute() ? $this->command->getCallbackQuery()->getFrom()->getId() : $this->command->getMessage()->getChat()->getId(),
            'text'                     => implode(PHP_EOL, $resultArray),
            'reply_markup'             => $this->afterKeyboard,
            'disable_web_page_preview' => true,
        ]);

    }
}
