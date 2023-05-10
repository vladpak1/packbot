<?php

namespace PackBot;

use Longman\TelegramBot\Commands\Command;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;

final class MainMenuScreen extends Screen
{
    protected Command $command;

    protected InlineKeyboard|Keyboard $keyboard;

    protected Text $text;

    protected string $screenName = 'MainMenu';

    public bool $blockSideExecute = false;

    public function __construct(Command $command)
    {
        parent::__construct($command);
        $this->command = $command;
        $this->prepareKeyboard();
        $this->text = new Text();
    }

    public function executeScreen(): ServerResponse
    {
        if($this->blockSideExecute) {
            $message = $this->command->getMessage() ?? $this->command->getCallbackQuery()->getMessage();
            $chat_id = $message->getChat()->getId();

            return Request::sendMessage([
                'chat_id'      => $chat_id,
                'text'         => $this->text->e('Вы в главном меню. Что вы хотите?'),
                'reply_markup' => $this->keyboard,
            ]);
        }

        return $this->maybeSideExecute('Вы в главном меню. Что вы хотите?', $this->keyboard);
    }

    public function executeCallback(string $callback): ServerResponse
    {
        switch($callback) {
            default:
                error_log('An attempt to execute undefined callback for screen ' . $this->screenName . ': ' . $callback);

                return $this->sendSomethingWrong();
            case 'siteMonitoring':
                $screen = new ListSitesScreen($this->command);
                $screen->executeScreen();

                return $this->command->getCallbackQuery()->answer();
            case 'domainChecks':
                $screen = new DomainChecksScreen($this->command);
                $screen->executeScreen();

                return $this->command->getCallbackQuery()->answer();
            case 'settings':
                $screen = new SettingsScreen($this->command);
                $screen->executeScreen();

                return $this->command->getCallbackQuery()->answer();
            case 'otherChecks':
                $screen = new OtherChecksScreen($this->command);
                $screen->executeScreen();

                return $this->command->getCallbackQuery()->answer();
            case 'seoChecks':
                $screen = new SeoChecksScreen($this->command);
                $screen->executeScreen();

                return $this->command->getCallbackQuery()->answer();
            case 'scripts':
                $screen = new ScriptsScreen($this->command);
                $screen->executeScreen();

                return $this->command->getCallbackQuery()->answer();
            case 'qrCode':
                $screen = new QRCodeScreen($this->command);
                $screen->executeScreen();

                return $this->command->getCallbackQuery()->answer();
        }
    }

    public function blockSideExecute(): self
    {
        $this->blockSideExecute = true;

        return $this;
    }

    protected function prepareKeyboard()
    {
        $this->keyboard = new InlineKeyboard(
            [
                [
                    'text'          => '🖥 ' . $this->text->e('Мониторинг сайтов'),
                    'callback_data' => 'MainMenu_siteMonitoring',
                ],
            ],
            [
                [
                    'text'          => '🔍 ' . $this->text->e('Проверка доменов'),
                    'callback_data' => 'MainMenu_domainChecks',
                ],
                [
                    'text'          => '📈 ' . $this->text->e('SEO проверки'),
                    'callback_data' => 'MainMenu_seoChecks',
                ],
            ],
            [
                [
                    'text'          => '🛠️ ' . $this->text->e('Другие проверки'),
                    'callback_data' => 'MainMenu_otherChecks',
                ],
                [
                    'text'          => '📜 ' . $this->text->e('Скрипты'),
                    'callback_data' => 'MainMenu_scripts',
                ],
            ],
            [
                [
                    'text'          => '🔳 ' . $this->text->e('Генератор QR-кодов'),
                    'callback_data' => 'MainMenu_qrCode',],
            ],
            [
                [
                    'text'          => '⚙️ ' . $this->text->e('Настройки'),
                    'callback_data' => 'MainMenu_settings',
                ],
            ],
        );
    }
}
