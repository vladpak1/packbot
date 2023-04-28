<?php
namespace PackBot;

use Longman\TelegramBot\Commands\Command;
use Longman\TelegramBot\Entities\ServerResponse;

final class IncidentScreen extends Screen {


    protected Command $command;

    protected Text $text;

    protected string $screenName = 'Incident';

    public function __construct(Command $command) {
        parent::__construct($command);
        $this->command = $command;
        $this->text    = new Text();
    }

    public function executeScreen(): ServerResponse {
        throw new ScreenException('This screen is not intended to be executed directly.');
    }

    public function executeCallback(string $callback): ServerResponse {
        switch($callback) {
            default:
                error_log('An attempt to execute undefined callback for screen ' . $this->screenName . ': ' . $callback);
                return $this->sendSomethingWrong();
        }
    }

    public function executeCallbackWithAdditionalData(string $callback, string $additionalData): ServerResponse {
        return match($callback) {
            default               => $this->sendSomethingWrong(),
            'back'                => $this->back($additionalData),
            'incidentScreen'      => $this->incidentScreen($additionalData),
        };
    }

    protected function incidentScreen(int $incidentID) {

        $incident = new Incident($incidentID);
        $siteID   = $incident->getSiteID();
        $time     = new Time();
        $resolved = $incident->isIncidentResolved();

        switch ($incident->getType()) {
            case 'wrongCode':
                if ($resolved) {
                    $incidentDescriptionString = implode(PHP_EOL, array(
                        $this->text->sprintf(
                            'При проверке сайта %s %s %s (%s) было обнаружено, что сайт возвращает код %d.',
                            $incident->getSite()->getURL(),
                            $time->convertMySQLDateTimeToHumanReadableDateTime($incident->getStartTime()),
                            $time->getServerTimezone(),
                            $incident->getStartTimeString(),
                            $incident->getData()['code']
                        ),
                        $this->text->sprintf(
                            'Сайт продолжал возвращать неверный код ответа до %s %s (%s).',
                            $time->convertMySQLDateTimeToHumanReadableDateTime($incident->getEndTime()),
                            $time->getServerTimezone(),
                            $incident->getEndTimeString()
                        ),
                        $this->text->sprintf(
                            'Таким образом, инцидент длился %s и на данный момент закрыт.',
                            $incident->getDurationString()
                        ),

                    ));
                } else {
                    $incidentDescriptionString = implode(PHP_EOL, array(
                        $this->text->sprintf(
                            'При проверке сайта %s %s %s (%s) было обнаружено, что сайт возвращает код %d.',
                            $incident->getSite()->getURL(),
                            $time->convertMySQLDateTimeToHumanReadableDateTime($incident->getStartTime()),
                            $time->getServerTimezone(),
                            $incident->getStartTimeString(),
                            $incident->getData()['code']
                        ),
                        $this->text->sprintf(
                            'С момента обнаружения сбоя прошло %s, но сайт все еще возвращает неверный код ответа.',
                            $incident->getDurationString()
                        ),
                    ));
                }
                break;
            case 'timeout':
                if ($resolved) {
                    $incidentDescriptionString = implode(PHP_EOL, array(
                        $this->text->sprintf(
                            'При проверке сайта %s %s %s (%s) было обнаружено, что сайт слишком долго не отвечает - время ответа составило %d секунд.',
                            $incident->getSite()->getURL(),
                            $time->convertMySQLDateTimeToHumanReadableDateTime($incident->getStartTime()),
                            $time->getServerTimezone(),
                            $incident->getStartTimeString(),
                            $incident->getData()['timeout']
                        ),
                        $this->text->sprintf(
                            'Сайт продолжал работать медленно до %s %s (%s).',
                            $time->convertMySQLDateTimeToHumanReadableDateTime($incident->getEndTime()),
                            $time->getServerTimezone(),
                            $incident->getEndTimeString()
                        ),
                        $this->text->sprintf(
                            'Таким образом, инцидент длился %s и на данный момент закрыт.',
                            $incident->getDurationString()
                        ),

                    ));
                } else {
                    $incidentDescriptionString = implode(PHP_EOL, array(
                        $this->text->sprintf(
                            'При проверке сайта %s %s %s (%s) было обнаружено, что сайт слишком долго не отвечает - время ответа составило %d секунд.',
                            $incident->getSite()->getURL(),
                            $time->convertMySQLDateTimeToHumanReadableDateTime($incident->getStartTime()),
                            $time->getServerTimezone(),
                            $incident->getStartTimeString(),
                            $incident->getData()['timeout']
                        ),
                        $this->text->sprintf(
                            'С момента обнаружения сбоя прошло %s, но сайт все еще работает медленно.',
                            $incident->getDurationString()
                        ),
                    ));
                }
                break;
        }



        $message = array(
            $this->text->sprintf('<b>%s инцидент #%d</b>', ($resolved ? $this->text->e('Закрытый'): $this->text->e('Открытый')),$incidentID),
            '',
            $incidentDescriptionString,
        );
        


        $keyboard = new MultiRowInlineKeyboard(array(
            array(
                'text'          => $this->text->e('Назад ⬅️'),
                'callback_data' => $this->screenName . '_back_' . $siteID,
            )
        ), -1);


        return $this->maybeSideExecute($message, $keyboard, true, array(
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ));
    }

    protected function back($siteID) {
        $callbackExecutor = new CallbackExecutor($this->command);

        return $callbackExecutor->forceCallback('IncidentsList_incidentsList_' . $siteID);
    }
}
