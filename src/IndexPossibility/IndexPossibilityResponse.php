<?php

namespace PackBot;

class IndexPossibilityResponse extends Entity implements \JsonSerializable
{
    protected array $response;

    protected bool $isOK;

    /**
     * IndexPossibilityResponse is a class that represents the response of the IndexPossibility request.
     * The response can be converted to json.
     *
     * @method bool   isOK()                   Check if the response is OK.
     * @method object getRawData()             Get the data as an object.
     * @method bool   isIndexBlocked()         Check if the page is blocked by robots.txt or by noindex meta tag.
     * @method bool   isIndexBlockedByRobots() Check if the page is blocked by robots.txt.
     * @method bool   isIndexBlockedByPage()   Check if the page is blocked by noindex meta tag.
     * @method string getEffectiveUrl()        Get the effective URL.
     * @method string jsonSerialize()          Serialize to json all properties of the class.
     * @method string __toString()             Converting to string.
     */
    public function __construct(array $response)
    {
        $this->response = $response;
        $this->isOK     = $response['isOK'] ?? false;
    }

    public function jsonSerialize(): string
    {
        return json_encode($this->response);
    }

    public function getRawData(): object
    {
        return (object) $this->response;
    }

    public function __toString(): string
    {
        return $this->jsonSerialize();
    }

    /**
     * Determine if the page is blocked by robots.txt or by noindex meta tag.
     */
    public function isIndexBlocked(): bool
    {
        return $this->response['indexBlockByRobots'] || $this->response['indexBlockByPage'];
    }

    public function isIndexBlockedByRobots(): bool
    {
        return $this->response['indexBlockByRobots'];
    }

    public function isIndexBlockedByPage(): bool
    {
        return $this->response['indexBlockByPage'];
    }

    public function getEffectiveUrl(): string
    {
        return $this->response['effectiveUrl'];
    }

    public function isOK(): bool
    {
        return true; // because it's already checked in IndexPossibility
    }
}
