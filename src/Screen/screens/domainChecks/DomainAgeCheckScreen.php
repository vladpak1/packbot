<?php

namespace PackBot;

use Longman\TelegramBot\Commands\Command;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;

final class DomainAgeCheckScreen extends Screen implements ScreenInterface
{
    protected Command $command;

    protected Keyboard|InlineKeyboard $keyboard;

    protected Keyboard $afterKeyboard;

    protected Text $text;

    protected Conversation $conversation;

    protected string $screenName = 'DomainAgeCheck';

    protected int $maxDomainsAtOnce;

    public function __construct(Command $command)
    {
        parent::__construct($command);
        $this->prepareKeyboard();
        $this->prepareAfterKeyboard();
        $this->command          = $command;
        $this->text             = new Text();
        $this->maxDomainsAtOnce = Environment::var('tools_settings')['WhoisTool']['maxDomainsAtOnce'];
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
                $screen = new DomainChecksScreen($this->command);
                $screen->executeScreen();

                return $this->command->getCallbackQuery()->answer();
            case 'anotherCheck':
                $screen = new DomainAgeCheckScreen($this->command);
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
                    'Этот инструмент позволяет узнать возраст домена.',
                    $this->text->sprintf('Максимальное количество доменов для проверки: %d', $this->maxDomainsAtOnce),
                    '',
                    'Каждый домен с новой строки.',
                    '<b>Отправьте домен или список доменов</b>',
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

        $message      = $this->getText();
        $inputDomains = array_unique(explode(PHP_EOL, $message));

        if (count($inputDomains) > $this->maxDomainsAtOnce) {
            return $this->tryToSendMessage($this->text->concatEOL(
                $this->text->sprintf('Максимальное количество доменов для проверки: %d. Вы отправили %d доменов.', $this->maxDomainsAtOnce, count($inputDomains)),
                'Сократите количество доменов и попробуйте еще раз.',
            ));
        }

        foreach ($inputDomains as $key => $domain) {
            $inputDomains[$key] = trim($domain);

            if (empty($inputDomains[$key])) {
                unset($inputDomains[$key]);
            }

            if (Url::isValid($inputDomains[$key])) {
                $inputDomains[$key] = Url::getDomain($inputDomains[$key]);
            } else {
                $this->tryToSendMessage(implode('', [
                    $this->text->sprintf('Некорректный домен: %s', $inputDomains[$key]),
                    PHP_EOL,
                    $this->text->e('Проверьте правильность ввода и попробуйте еще раз.'),
                ]));

                return Request::emptyResponse();

            }
        }

        $conversation->stop();

        $response        = $this->tryToSendMessage($this->text->e('Проверка выполняется... Скоро здесь появится отчет.'), false);
        $reportMessageID = $response->getResult()->getMessageId();

        try {
            $tool   = new WhoisTool($inputDomains);
            $result = $tool->getDomainsAge();
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

        $report->setTitle('Ваш отчет готов! Возраст доменов:');

        foreach ($result as $domain => $info) {

            $report->addBlock([
                '[' . $domain . ']: ',
                'был создан',
                ' ',
                $info['relativeTimeString'],
                ', ',
                'создан',
                ' ',
                $info['creationDateString'],
                ', ',
                'истекает',
                ' ',
                $info['expirationDateString'],
                ]);
        }

        Request::editMessageText([
            'chat_id'                  => $this->isSideExecute() ? $this->command->getCallbackQuery()->getFrom()->getId() : $this->command->getMessage()->getChat()->getId(),
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
