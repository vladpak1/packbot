<?php

namespace PackBot;

use Longman\TelegramBot\Commands\Command;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;

final class AddSiteScreen extends Screen implements ScreenInterface
{
    protected Command $command;

    protected Keyboard|InlineKeyboard $keyboard;

    protected Keyboard $afterKeyboard;

    protected Text $text;

    protected Conversation $conversation;

    protected string $screenName = 'AddSite';

    protected int $maxDomainsAtOnce;

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
                $screen = new ListSitesScreen($this->command);
                $screen->executeScreen();

                return $this->command->getCallbackQuery()->answer();
            case 'anotherSite':
                $screen = new self($this->command);
                $screen->executeScreen();

                return $this->command->getCallbackQuery()->answer();
            case 'mySites':
                $screen = new ListSitesScreen($this->command);
                $screen->executeScreen();

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
                    'Отправьте сайт, чтобы добавить его в список мониторинга.',
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
                'text'          => $this->text->e('Добавить еще один сайт'),
                'callback_data' => $this->screenName . '_anotherSite',
            ],
            [
                'text'          => $this->text->e('Мои сайты'),
                'callback_data' => $this->screenName . '_mySites',
            ],
            [
                'text'          => $this->text->e('Назад ⬅️'),
                'callback_data' => $this->screenName . '_back',
            ],
        ], 2);
    }

    private function proccessCheckRequest($conversation)
    {

        $message = $this->getText();

        if (empty($message) || count(explode(PHP_EOL, $message)) > 1 || !Url::isValid($message) || !str_contains($message, '.') || str_contains($message, ' ')) {
            return $this->tryToSendMessage($this->text->e('Некорректный сайт. Пожалуйста, убедитесь, что вы отправили только один корректный адрес сайта и попробуйте еще раз.'));
        }

        $conversation->stop();

        $response = $this->tryToSendMessage($this->text->e('Добавляю сайт... Это может занять некоторое время.'), false);
        sleep(1);
        $reportMessageID = $response->getResult()->getMessageId();

        $siteManager = new SiteManager($this->getUserID());

        try {
            $siteManager->addSite(trim(mb_strtolower($message)));
        } catch (InvalidDomainException $e) {
            return Request::editMessageText([
                'chat_id'      => $this->isSideExecute() ? $this->command->getCallbackQuery()->getFrom()->getId() : $this->command->getMessage()->getChat()->getId(),
                'message_id'   => $reportMessageID,
                'text'         => $this->text->e('Некорректный сайт. Пожалуйста, убедитесь, что вы отправили только один корректный адрес сайта и попробуйте еще раз.') . ' ' . $e->getMessage(),
                'parse_mode'   => 'HTML',
                'reply_markup' => $this->afterKeyboard,
            ]);
        } catch (SiteMonitoringException $e) {
            return Request::editMessageText([
                'chat_id'      => $this->isSideExecute() ? $this->command->getCallbackQuery()->getFrom()->getId() : $this->command->getMessage()->getChat()->getId(),
                'message_id'   => $reportMessageID,
                'text'         => $this->text->sprintf('Не удалось добавить сайт. Причина: %s', $this->text->e($e->getMessage())),
                'parse_mode'   => 'HTML',
                'reply_markup' => $this->afterKeyboard,
            ]);
        }
        sleep(5);
        Request::editMessageText([
            'chat_id'                  => $this->isSideExecute() ? $this->command->getCallbackQuery()->getFrom()->getId() : $this->command->getMessage()->getChat()->getId(),
            'message_id'               => $reportMessageID,
            'text'                     => $this->text->sprintf('Сайт %s успешно добавлен в мониторинг.', Url::getEffectiveUrl($message)),
            'parse_mode'               => 'HTML',
            'reply_markup'             => $this->afterKeyboard,
            'disable_web_page_preview' => true,
        ]);

        return Request::emptyResponse();
    }
}
