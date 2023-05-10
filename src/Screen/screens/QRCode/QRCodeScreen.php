<?php

namespace PackBot;

use Longman\TelegramBot\Commands\Command;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\ServerResponse;

final class QRCodeScreen extends Screen
{
    protected Command $command;

    protected Keyboard $keyboard;

    protected Text $text;

    protected string $screenName = 'QRCode';

    public function __construct(Command $command)
    {
        parent::__construct($command);
        $this->command = $command;
        $this->text    = new Text();
        $this->prepareKeyboard();
    }

    public function executeScreen(): ServerResponse
    {
        return $this->maybeSideExecute('С помощью этих инструментов вы можете сгенерировать QR-код для ссылки.', $this->keyboard);
    }

    public function executeCallback(string $callback): ServerResponse
    {
        switch($callback) {
            default:
                error_log('An attempt to execute undefined callback for screen ' . $this->screenName . ': ' . $callback);

                return $this->sendSomethingWrong();
            case 'QRSimple':
                $screen = new QRSimpleScreen($this->command);
                $screen->executeScreen();

                return $this->command->getCallbackQuery()->answer();
            case 'QRWithLogo':
                $screen = new QRWithLogoScreen($this->command);
                $screen->executeScreen();

                return $this->command->getCallbackQuery()->answer();

        }
    }

    protected function prepareKeyboard()
    {
        $this->keyboard = new MultiRowInlineKeyboard([
            [
                'text'          => $this->text->e('Простой QR-код'),
                'callback_data' => $this->screenName . '_QRSimple',
            ],
            [
                'text'          => $this->text->e('QR-код с логотипом'),
                'callback_data' => $this->screenName . '_QRWithLogo',
            ],
            [
                'text'          => $this->text->e('Назад ⬅️'),
                'callback_data' => 'DomainChecks_backToMainMenu',
            ],
        ], -1);
    }
}
