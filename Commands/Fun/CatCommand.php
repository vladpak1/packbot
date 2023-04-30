<?php
namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use PackBot\Cats;
use PackBot\Text;
use PackBot\UserSettings;

class CatCommand extends UserCommand {
    protected $name         = 'cat';
    protected $description  = 'Use this command to get a random cat image.';
    protected $usage        = '/cat';
    protected $version      = '1.0.0';

    public function execute(): ServerResponse {

        /**
         * Set userID to global static variable.
         */
        UserSettings::setUserID($this->getMessage()->getFrom()->getId());

        $text     = new Text();
        $chatID   = $this->getMessage()->getChat()->getId();

        try {
            $cats = new Cats($this->getMessage()->getFrom()->getId());
            $cat  = $cats->get();

            if (!$cat) throw new \Exception('Котиков не осталось. Используйте /catreload, чтобы начать заново. 😿');

        } catch (\Exception $e) {
            return Request::sendMessage([
                'chat_id' => $chatID,
                'text'    => $text->e($e->getMessage()),
            ]);
        }
        
        $animated = array(
            'gif',
            'mp4',
        );
        
        $extension = pathinfo($cat, PATHINFO_EXTENSION);

        if (in_array($extension, $animated)) {
            return Request::sendAnimation([
                'chat_id' => $chatID,
                'animation' => Request::encodeFile($cat),
                'caption' => $text->e('Еще - /cat 🐱'),
            ]);
        } else {
            return Request::sendPhoto([
                'chat_id' => $chatID,
                'photo' => Request::encodeFile($cat),
                'caption' => $text->e('Еще - /cat 🐱'),
            ]);
        }   
    }
}
