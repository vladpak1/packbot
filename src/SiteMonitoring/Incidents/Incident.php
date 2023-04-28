<?php

namespace PackBot;

class Incident extends Entity {

    protected int $id;

    protected array $incident;

    /**
     * This class is representing an incident with a site.
     * 
     * @param int $incidentID ID of the incident.
     * @throws IncidentException
     */
    public function __construct(int $incidentID) {
        $this->id       = $incidentID;
        $this->incident = $this->getIncidentFromDB();
    }

    /**
     * Returns ID of the incident.
     */
    public function getID(): int {
        return $this->id;
    }

    /**
     * Returns ID of the site that is related to the incident.
     */
    public function getSiteID(): int {
        return $this->incident['site_id'];
    }

    public function getSite(): Site {
        return new Site($this->getSiteID());
    }

    /**
     * Returns ID of the site that is related to the incident.
     */
    public function isIncidentResolved(): bool {
        return !empty($this->incident['end_time']);
    }

    public function getData(): ?array {
        return $this->incident['data'] !== '' ? unserialize($this->incident['data']) : null;
    }

    public function getType(): string {
        return $this->getData()['type'];
    }

    public function getTypeString(): string {
        return match($this->getType()) {
            'wrongCode' => 'Неверный код ответа',
            'timeout'   => 'Долгое ожидание ответа',
            default => 'Неизвестно',
        };
    }

    /**
     * Get incident duration in seconds.
     */
    public function getDuration(): int {
        $time = new Time();

        return $time->getTimestampFromDatetime($this->getEndTime()) - $time->getTimestampFromDatetime($this->getStartTime());
    }

    public function getDurationString(): string {
        $time = new Time();

        return $time->secondsToHumanReadable($this->getDuration());
    }

    public function getStartTime() {
        return $this->incident['start_time'];
    }

    /**
     * Returns end time of the incident.
     * If incident is not resolved, returns current time.
     */
    public function getEndTime() {
        return $this->isIncidentResolved() ? $this->incident['end_time'] : date('Y-m-d H:i:s');
    }

    /**
     * Returns relative time of the incident start.
     */
    public function getStartTimeString(): string {
        $time = new Time();

        return $time->timestampToUltimateHumanReadableRelativeTime($time->getTimestampFromDatetime($this->getStartTime()));
    }

    public function getEndTimeString(): string|false {
        if (!$this->isIncidentResolved()) return false;
        $time = new Time();

        return $time->timestampToUltimateHumanReadableRelativeTime($time->getTimestampFromDatetime($this->getEndTime()));
    }

    /**
     * Returns ID of the site that is related to the incident.
     */
    public function resolve() {
        if ($this->isIncidentResolved()) throw new IncidentException("Incident with ID $this->id is already resolved.");
        IncidentsDB::resolveIncident($this->id);
        $this->incident = $this->getIncidentFromDB();
    }

    public function jsonSerialize(): string {
        return json_encode($this->incident);
    }

    public function __toString(): string {
        return $this->jsonSerialize();
    }

    public function getRawData(): object {
        return (object) $this->incident;
    }

    protected function getIncidentFromDB() {
        $incident = IncidentsDB::getIncident($this->id);
        if (!$incident) throw new IncidentException("Incident with ID $this->id does not exist.");
        return $incident;
    }

}