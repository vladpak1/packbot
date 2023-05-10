<?php

namespace PackBot;

use Longman\TelegramBot\Commands\Command;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;

final class SitemapParserScreen extends Screen implements ScreenInterface
{
    protected Command $command;

    protected Keyboard|InlineKeyboard $keyboard;

    protected Keyboard $afterKeyboard;

    protected Text $text;

    protected Conversation $conversation;

    protected string $screenName = 'SitemapParser';

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
                    'Этот инструмент позволяет собрать все ссылки из sitemap.xml файла.',
                    $this->text->sprintf('Инструмент поддерживает вложенные сайтмапы, но их количество не должно превышать %d.', Environment::var('tools_settings')['SitemapParserTool']['maxSitemapsAtOnce']),
                    'Результат будет отправлен как текстовый файл.',
                    '',
                    '<b>Отправьте ссылку на сайтмап файл.</b>',
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
        $inputDomain = $this->getText();

        if (count(explode(PHP_EOL, $inputDomain)) > 1) {
            return $this->tryToSendMessage($this->text->e('Проверка нескольких доменов одновременно не поддерживается этим инструментом. Уберите лишние домены и попробуйте еще раз.'), false);
        }

        if (empty($inputDomain) || !Url::isValid($inputDomain)) {
            return $this->tryToSendMessage($this->text->e('Некорректный URL. Проверьте правильность ввода и повторите попытку.'), false);
        }

        $conversation->stop();

        $response        = $this->tryToSendMessage($this->text->e('Запускаю парсер сайтмапов...'), false);
        $reportMessageID = $response->getResult()->message_id;

        /**
         * We're moving away from our traditional tool architecture to provide
         * the best possible user experience by allowing the user to watch the scraping process take place.
         * Isn't the user the most important thing?
         */
        try {
            sleep(2);
            $toolSettings  = Environment::var('tools_settings')['SitemapParserTool'];
            $sitemapParser = new SitemapParser($inputDomain);
            $result        = $sitemapParser
                                    ->setMaxSitemapsAtOnce($toolSettings['maxSitemapsAtOnce'])
                                    ->setTimeLimit($toolSettings['timeLimit'])
                                    ->setSitemapCurlWaitTime($toolSettings['sitemapCurlWaitTime'])
                                    ->setTelegramProgressMessage([
                                        'chat_id'    => $this->isSideExecute() ? $this->command->getCallbackQuery()->getFrom()->getId() : $this->command->getMessage()->getChat()->getId(),
                                        'message_id' => $reportMessageID,
                                    ])
                                    ->execute()
                                    ->getLinks();
        } catch (SitemapParserException $e) {
            sleep(1);

            return Request::editMessageText([
                'chat_id'      => $this->isSideExecute() ? $this->command->getCallbackQuery()->getFrom()->getId() : $this->command->getMessage()->getChat()->getId(),
                'message_id'   => $reportMessageID,
                'text'         => $this->text->e('Простите, но отчет не может быть создан. Ошибка:') . ' ' . $e->getMessage(),
                'parse_mode'   => 'HTML',
                'reply_markup' => $this->afterKeyboard,
            ]);
        }

        Request::deleteMessage([
            'chat_id'    => $this->isSideExecute() ? $this->command->getCallbackQuery()->getFrom()->getId() : $this->command->getMessage()->getChat()->getId(),
            'message_id' => $reportMessageID,
        ]);

        sleep(2);
        Request::sendDocument([
                'chat_id'                  => $this->isSideExecute() ? $this->command->getCallbackQuery()->getFrom()->getId() : $this->command->getMessage()->getChat()->getId(),
                'document'                 => Request::encodeFile(TempFile::txt(implode(PHP_EOL, $result), 'sitemapParsed')),
                'caption'                  => $this->text->sprintf('Все ссылки сайтмапа %s (%d)', $inputDomain, count($result)),
                'parse_mode'               => 'HTML',
                'disable_web_page_preview' => true,
        ]);

        return Request::sendMessage([
            'chat_id'                  => $this->isSideExecute() ? $this->command->getCallbackQuery()->getFrom()->getId() : $this->command->getMessage()->getChat()->getId(),
            'text'                     => $this->text->e('Результат отправлен как файл.'),
            'reply_markup'             => $this->afterKeyboard,
            'disable_web_page_preview' => true,
            ]);

    }
}
