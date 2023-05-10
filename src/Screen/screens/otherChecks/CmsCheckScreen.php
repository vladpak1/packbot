<?php

namespace PackBot;

use Longman\TelegramBot\Commands\Command;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;

final class CmsCheckScreen extends Screen implements ScreenInterface
{
    protected Command $command;

    protected Keyboard|InlineKeyboard $keyboard;

    protected Keyboard $afterKeyboard;

    protected Text $text;

    protected Conversation $conversation;

    protected string $screenName = 'CmsCheck';

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
                $screen = new OtherChecksScreen($this->command);
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
                    'Этот инструмент позволяет узнать CMS сайта.',
                    '',
                    'Обратите внимание, что этот инструмент работает не очень хорошо. Возможно, в будущем он будет улучшен.',
                    '',
                    '<b>Отправьте домен</b>',
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
            return $this->tryToSendMessage($this->text->e('Некорректный домен. Проверьте правильность ввода и повторите попытку.'), false);
        }

        $conversation->stop();

        $response        = $this->tryToSendMessage($this->text->e('Проверка выполняется... Скоро здесь появится отчет.'), false);
        $reportMessageID = $response->getResult()->getMessageId();

        try {
            $tool   = new CmsCheckTool($inputDomain);
            $result = $tool->getResult()[$inputDomain];
        } catch (ToolException $e) {
            sleep(1);

            return Request::editMessageText([
                'chat_id'      => $this->isSideExecute() ? $this->command->getCallbackQuery()->getFrom()->getId() : $this->command->getMessage()->getChat()->getId(),
                'message_id'   => $reportMessageID,
                'text'         => $this->text->e('Простите, но отчет не может быть создан. Ошибка:') . ' ' . $e->getMessage(),
                'parse_mode'   => 'HTML',
                'reply_markup' => $this->afterKeyboard,
                ]);
        }

        $report = new Report();

        if ('unknown' == $result['cms']) {
            $report
                ->setTitle('Ваш отчет готов!')
                ->addBlock("[$inputDomain]:" . ' ' . $this->text->e('Не удалось определить CMS. Возможно, это статический сайт, или сайт использует CMS, которая не поддерживается этим инструментом.'));
        } else {
            $report
                ->setTitle('Ваш отчет готов!')
                ->addBlock("[$inputDomain]:")
                ->addBlock($this->text->sprintf('Скорее всего, сайт использует <b>%s</b>', '<a href="https://google.com/search?q=' . $result['cms'] . '">' . $result['cms'] . '</a>'));
        }

        Request::editMessageText([
            'chat_id'                  => $this->getChatID(),
            'message_id'               => $reportMessageID,
            'text'                     => $report->getReport(),
            'parse_mode'               => 'HTML',
            'reply_markup'             => $this->afterKeyboard,
            'disable_web_page_preview' => true,
        ]);

        $this->tryToSendTempMessage('Один из отчетов готов! Посмотрите в чат.', 2);

        return Request::emptyResponse();
    }
}
