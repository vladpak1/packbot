<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use PackBot\Text;
use PackBot\UserSettings;

class HelpCommand extends UserCommand
{
    protected $name = 'help';

    protected $description = 'Help command.';

    protected $usage = '/help';

    protected $version = '1.0.0';

    public function execute(): ServerResponse
    {

        /**
         * Set userID to global static variable.
         */
        UserSettings::setUserID($this->getMessage()->getFrom()->getId());

        $text = new Text();

        $commands = [
            '/start'    => $text->e('Запускает бота.'),
            '/list'     => $text->e('Быстрый доступ к списку сайтов.'),
            '/reload'   => $text->e('Перезагружает бота. Используйте, если что-то пошло не так.'),
            '/help'     => $text->e('Показывает это сообщение.'),
            '/language' => $text->e('Открывает меню выбора языка.'),
        ];

        $genericMessage = $text->concatEOL(
            'Доступные команды:',
            implode("\n", array_map(function ($key, $value) use ($text) {
                return implode(' - ', [$key, $value]);
            }, array_keys($commands), $commands)),
            'Бот работает с помощью инлайн кнопок, поэтому особой необходимости в командах нет.',
            'Если у вас какие-то проблемы или предложения, вы можете написать автору бота, ссылку на которого найдете в описании бота.',
        );

        return Request::sendMessage([
            'chat_id' => $this->getMessage()->getChat()->getId(),
            'text'    => $genericMessage,
        ]);
    }
}
