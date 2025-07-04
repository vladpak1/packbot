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
                $siteManager  = new SiteManager($this->getUserID());
                $currentQuota = Environment::var('monitoring_settings')['maxSitesPerUser'] - count($siteManager->getSites());

                return $this->maybeSideExecute(
                    $this->text->concatEOL(
                        'Отправьте сайт или список сайтов, чтобы добавить его в список мониторинга.',
                        '',
                        $this->text->sprintf('Каждый сайт должен быть на отдельный строке. Вы можете добавить еще %d сайтов.', $currentQuota),
                        '',
                        'Может потребоваться внести в белый список нашего бота, чтобы он смог проверять ваш сайт. Вы можете узнать его:',
                        'По useragent: <code>PackBot/2.0 (+https://t.me/packhelperbot)</code>',
                        'По IP-адресу: <code>212.162.153.137</code>'
                    ),
                    $this->keyboard,
                    false,
                    [
                    'parse_mode' => 'HTML',
                ]
                );
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

    private function proccessCheckRequest(Conversation $conversation)
    {

        $message = $this->getText();

        $sites = explode(PHP_EOL, $message);

        if (0 === count($sites) || !str_contains($message, '.')) {
            return $this->tryToSendMessage($this->text->e('Неправильный формат. Вы должны отправить один корректный сайт или список сайтов - каждый с новой строки. Попробуйте еще раз.'));
        }

        $sites = array_unique(array_map('trim', array_filter($sites)));

        if (count($sites) > Environment::var('monitoring_settings')['maxSitesPerUser']) {
            return $this->tryToSendMessage(
                $this->text->sprintf(
                    'Вы можете добавить не более %d сайтов в мониторинг. Попробуйте еще раз, сократив список до этого количества.',
                    Environment::var('monitoring_settings')['maxSitesPerUser']
                )
            );
        }

        $conversation->stop();
        $success = [];
        $error   = [];

        foreach ($sites as $key => $site) {
            if (!Url::isValid($site)) {
                unset($sites[$key]);
                $error[] = [
                    'site'   => $site,
                    'reason' => $this->text->e('Некорректный адрес сайта.'),
                ];

            }
        }

        $response          = $this->tryToSendMessage($this->text->e('Добавляю сайты... Это может занять некоторое время.'));
        $responseMessageID = $response->getResult()->getMessageId();

        $siteManager = new SiteManager($this->getUserID());

        foreach ($sites as $site) {
            try {
                $result = $siteManager->addSite($site);

                if ($result) { // get last site
                    $usersSites = $siteManager->getSites();
                    /**
                     * @var Site $newSite
                     */
                    $newSite   = end($usersSites);
                    $success[] = $newSite->getURL();
                } else {
                    throw new \Exception('Неизвестная ошибка базы данных.');
                }
            } catch (\Throwable $e) {
                $error[] = [
                    'site'   => $site,
                    'reason' => $e->getMessage(),
                ];
            }

        }
        $successMessage = '';

        if (count($success) > 0) {
            $successMessage = $this->text->sprintf('%d сайтов успешно добавлено в мониторинг:', count($success));

            foreach ($success as $site) {
                $successMessage .= PHP_EOL . '✅ ' . $site;
            }
        }

        $errorMessage = '';

        if (count($error) > 0) {
            $errorMessage = PHP_EOL . $this->text->e('<b>Некоторые сайты не удалось добавить в мониторинг:</b>');

            foreach ($error as $item) {
                $errorMessage .= PHP_EOL . $this->text->sprintf(
                    '⚠️ %s - %s',
                    $item['site'],
                    $item['reason']
                );
            }
        }

        return Request::editMessageText([
            'chat_id'    => $this->isSideExecute() ? $this->command->getCallbackQuery()->getFrom()->getId() : $this->command->getMessage()->getChat()->getId(),
            'message_id' => $responseMessageID,
            'text'       => $this->text->concatEOL(
                $successMessage,
                $errorMessage,
                '',
                'Вы можете добавить еще сайты или вернуться к списку уже добавленных.',
            ),
            'reply_markup'             => $this->afterKeyboard,
            'parse_mode'               => 'HTML',
            'disable_web_page_preview' => true,
        ]);

    }
}
