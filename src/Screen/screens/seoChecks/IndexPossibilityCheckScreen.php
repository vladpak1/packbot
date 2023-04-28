<?php
namespace PackBot;

use Longman\TelegramBot\Commands\Command;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;

final class IndexPossibilityCheckScreen extends Screen implements ScreenInterface {

    protected Command $command;

    protected Keyboard|InlineKeyboard $keyboard;

    protected Keyboard $afterKeyboard;

    protected Text $text;

    protected Conversation $conversation;

    protected string $screenName = 'IndexPossibilityCheck';

    protected int $maxDomainsAtOnce;

    public function __construct(Command $command) {
        parent::__construct($command);
        $this->prepareKeyboard();
        $this->prepareAfterKeyboard();
        $this->command = $command;
        $this->text = new Text();
        $this->maxDomainsAtOnce = Environment::var('tools_settings')['IndexPossibilityCheckTool']['maxDomainsAtOnce'];
    }

    public function executeScreen(): ServerResponse {
        return $this->startConversation();
    }

    public function executeCallback(string $callback): ServerResponse {

        $this->loadConversation();
        $this->conversation->stop();

        switch($callback) {
            default:
                error_log('An attempt to execute undefined callback for screen ' . $this->screenName . ': ' . $callback);
                return $this->sendSomethingWrong();
            case 'back':
                $screen = new SeoChecksScreen($this->command);
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

    protected function startConversation() {
        
        if (!isset($this->conversation)) $this->conversation = new Conversation($this->getChatID(), $this->getUserID(), 'conversationHandler');

        $conversation     = &$this->conversation;
        $notes            = &$conversation->notes;
        !is_array($notes) && $notes
                          = array();

        $state               = $notes['state'] ?? 0;
        $notes['screenName'] = $this->screenName;

        //State machine
        switch ($state) {
            case 0:
                $notes['state'] = 1;
                $conversation->update();

                return $this->maybeSideExecute(array(
                    'Этот инструмент позволяет проверить, есть ли возможность индексации страницы.',
                    'Имейте в виду, что в данный момент проверяется только поле * в robots.txt, так что правила для отдельных ботов не учитываются. Возможно, я добавлю этот функционал позже.',
                    $this->text->sprintf('Максимальное количество доменов для проверки: %d', $this->maxDomainsAtOnce),
                    '',
                    'Каждый домен с новой строки.',
                    '<b>Отправьте домен или список доменов</b>',
                    ''
                ), $this->keyboard, true, array(
                    'parse_mode' => 'HTML',
                ));
            case 1:
                return $this->proccessCheckRequest($conversation);
            default:
                return $this->sendSomethingWrong();
        }
    }

    protected function forceReplyKeyboard() {
        return Keyboard::forceReply();
    }

    protected function prepareKeyboard() {

        $this->keyboard = new InlineKeyboard(array(
            array(
                'text' => $this->text->e('Назад ⬅️'),
                'callback_data' => $this->screenName . '_back',
            )
        ));
    }

    protected function prepareAfterKeyboard() {
        $this->afterKeyboard = new MultiRowInlineKeyboard(array(
            array(
                'text' => $this->text->e('Еще одна проверка'),
                'callback_data' => $this->screenName . '_anotherCheck',
            ),
            array(
                'text' => $this->text->e('Сохранить этот отчет'),
                'callback_data' => $this->screenName . '_saveReport',
            ),
            array(
                'text' => $this->text->e('Назад ⬅️'),
                'callback_data' => $this->screenName . '_back',
            )
        ), 2);
    }

    private function proccessCheckRequest($conversation) {

        $message = $this->getText();
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
                $inputDomains[$key] = $inputDomains[$key];
                } else {
                    $this->tryToSendMessage(implode('', array(
                        $this->text->sprintf('Некорректный домен: %s', Format::prepDisplay($inputDomains[$key], 15)),
                        PHP_EOL,
                        $this->text->e('Проверьте правильность ввода и попробуйте еще раз.'),
                    )));
                    return Request::emptyResponse();
                }
        }

        $conversation->stop();

        $response = $this->tryToSendMessage($this->text->e('Проверка выполняется... Скоро здесь появится отчет.'), false);
        $reportMessageID = $response->getResult()->getMessageId();

        try {
            $tool   = new IndexPossibilityCheckTool($inputDomains);
            $result = $tool->getResult();
        } catch (ToolException $e) {
            sleep(1);
            return Request::editMessageText(array(
                'chat_id' => $this->isSideExecute() ? $this->command->getCallbackQuery()->getFrom()->getId() : $this->command->getMessage()->getChat()->getId(),
                'message_id' => $reportMessageID,
                'text' => $this->text->e('Простите, но отчет не может быть создан. Ошибка:') . ' ' . $e->getMessage(),
                'parse_mode' => 'HTML',
                'reply_markup' => $this->afterKeyboard,
            ));
        }

        $report = new Report();

        $report->setTitle('Ваш отчет готов! Возможность индексации:');

        foreach ($result as $domain => $indexPossibilityResponse) {
            /**
             * @var IndexPossibilityResponse $indexPossibilityResponse
             */
            $report->addBlock(array(
                '[' . $indexPossibilityResponse->getEffectiveUrl() . ']: ',
                PHP_EOL,
                ($indexPossibilityResponse->isIndexBlocked() ? $this->text->e('🚫') : $this->text->e('✅ ')),
                ($indexPossibilityResponse->isIndexBlocked() ? $this->text->e('Индексация запрещена') : $this->text->e('Индексация разрешена')),
                PHP_EOL,
                'Индексация запрещена в robots.txt:',
                ' ',
                ($indexPossibilityResponse->isIndexBlockedByRobots() ? 'Да' : 'Нет'),
                PHP_EOL,
                'Индексация запрещена страницей (мета-теги или код ответа):',
                ' ',
                ($indexPossibilityResponse->isIndexBlockedByPage() ? 'Да' : 'Нет'),
            ));
        }

        Request::editMessageText(array(
            'chat_id' => $this->isSideExecute() ? $this->command->getCallbackQuery()->getFrom()->getId() : $this->command->getMessage()->getChat()->getId(),
            'message_id' => $reportMessageID,
            'text' => $report->getReport(),
            'parse_mode' => 'HTML',
            'reply_markup' => $this->afterKeyboard,
            'disable_web_page_preview' => true,
        ));

        $this->tryToSendTempMessage('Один из отчетов готов! Посмотрите в чат.', 2);

        return Request::emptyResponse();
    }
}
