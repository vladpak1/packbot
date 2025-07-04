<?php

namespace PackBot;

use Longman\TelegramBot\Request;

class Site implements \JsonSerializable
{
    protected array $data = [];

    protected Text $text;

    protected Time $time;

    /**
     * This class represents a site in the monitoring system.
     *
     * @method int    getID()             Get ID of the site.
     * @method string getURL()            Get URL of the site.
     * @method int    getLastCheck()      Get timestamp of the last check.
     * @method array  getAdditionalData() Get data of the site.
     *
     * @param  int                     $id ID of the site.
     * @throws SiteMonitoringException If site with such ID does not exist or database error occurred.
     */
    public function __construct(int $id)
    {
        $this->data = SiteMonitoringDB::getSite($id);
        $this->text = new Text();
        $this->time = new Time();
    }

    public function jsonSerialize(): array
    {
        return $this->data;
    }

    public function getAdditionalData(): array
    {
        return '' !== $this->data['data'] ? unserialize($this->data['data']) : [];
    }

    public function getID(): int
    {
        return $this->data['id'];
    }

    public function setDownState()
    {
        SiteMonitoringDB::setSiteDownState($this->getID());
    }

    public function setUpState()
    {
        SiteMonitoringDB::setSiteWorkingState($this->getID());

        /**
         * Workaround for the bug with unclosed incidents.
         */
        if (1 == $this->getRawState()) {
            IncidentsDB::closeAnyActiveIncidentsForSite($this->getID());
        }
    }

    public function getURL(): string
    {
        return $this->punycodeToUnicode($this->data['url']);
    }

    public function getState(): string
    {
        $state = $this->data['state'];

        return match($state) {
            default => $this->text->e('Неизвестно'),
            1       => $this->text->e('Работает'),
            0       => $this->text->e('Не работает'),
        };
    }

    public function getAlertsCount(): int
    {
        return $this->getAdditionalData()['alertCount'] ?? 0;
    }

    public function getRawState(): int|null
    {
        return $this->data['state'];
    }

    public function getLastCheck(): int
    {
        return $this->data['last_check'];
    }

    public function getFirstAlertSentTime()
    {
        return $this->time->timestampToUltimateHumanReadableRelativeTime(isset($this->getAdditionalData()['firstAlertSent']) ? $this->getAdditionalData()['firstAlertSent'] : 0);
    }

    public function getLastAlertSentTime()
    {
        return $this->time->timestampToUltimateHumanReadableRelativeTime(isset($this->getAdditionalData()['lastAlertSent']) ? $this->getAdditionalData()['lastAlertSent'] : 0);
    }

    public function getLastAlertTimestamp()
    {
        return $this->getAdditionalData()['lastAlertSent'] ?? 0;
    }

    public function getFirstAlertTimestamp()
    {
        return $this->getAdditionalData()['firstAlertSent'] ?? 0;
    }

    public function getIncidentID(): int|null
    {
        return $this->getAdditionalData()['incidentID'] ?? null;
    }

    /**
     * Get reason of the site being down.
     * If site is up, returns false.
     *
     * Reason array:
     * @var string $reason['type'] Type of the reason. Can be "timeout" or "wrongCode".
     * @var int    $reason['code'] HTTP code of the site. Only if type is "wrongCode".
     * @var int    $reason['timeout'] Timeout of the site. Only if type is "timeout".
     *
     * @return array|false Array of reasons or false if site is up.
     */
    public function getDownStateReason(): array|false
    {
        return $this->getAdditionalData()['reason'] ?? false;
    }

    /**
     * Get last check time in human-readable format.
     */
    public function getLastCheckTime(): string
    {
        return $this->time->timestampToUltimateHumanReadableRelativeTime($this->getLastCheck());
    }

    /**
     * Sends message to owners.
     *
     * @param  string $message Message to send. Message will not be translated.
     * @return int    Number of sent messages.
     */
    public function sendMessageToOwners(string $message): int
    {
        $owners = SiteMonitoringDB::getSiteOwners($this->getID());
        $sent   = 0;

        foreach ($owners as $userID) {
            $success = Request::sendMessage([
                'chat_id'                  => PackDB::getChatIDByUserID($userID),
                'text'                     => $message,
                'disable_web_page_preview' => true,
            ]);

            if ($success) {
                $sent++;
            }
        }

        return $sent;
    }

    function punycodeToUnicode(string $url): string
    {
        if (!function_exists('idn_to_utf8')) {
            // intl не установлен — просто вернём исходный URL
            return $url;
        }

        $parts = parse_url($url);

        $out = '';
        if (!empty($parts['scheme']))   $out .= $parts['scheme'] . ':';
        if (!empty($parts['host']))     $out .= '//';
        if (!empty($parts['user']))     $out .= $parts['user'];
        if (!empty($parts['pass']))     $out .= ':' . $parts['pass'];
        if (!empty($parts['user']))     $out .= '@';
        if (!empty($parts['host']))     $out .= idn_to_utf8($parts['host']);
        if (!empty($parts['port']))     $out .= ':' . $parts['port'];
        if (!empty($parts['path']))     $out .= $parts['path'];
        if (!empty($parts['query']))    $out .= '?' . $parts['query'];
        if (!empty($parts['fragment'])) $out .= '#' . $parts['fragment'];

        return $out;
    }
}
