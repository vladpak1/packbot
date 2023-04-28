<?php


namespace PackBot;


class PageSpeedResponse extends Entity implements \JsonSerializable {

    protected array $response;

    protected bool $isOK;

    protected array $summary;


    /**
     * PageSpeedResponse is a class that represents the response of the PageSpeed request.
     * The response can be converted to json.
     * 
     * @method bool isOK() Check if the response is OK.
     * @method object getRawData() Get the data as an object.
     * @method array getScreenshots() Get the screenshots.
     * @method array getSummary() Get the summary.
     * @method string jsonSerialize() Serialize to json all properties of the class.
     * @method string __toString() Converting to string.
     * 
     * @param string $response The response of the PageSpeed request.
     */
    public function __construct(string $response) {
        $this->response = json_decode($response, true);
        $this->createSummary();
    }

    public function jsonSerialize(): string {
        return json_encode($this->response);
    }

    public function getRawData(): object {
        return (object) $this->response;
    }

    /**
     * @todo
     */
    public function getScreenshots(): array {
        return array(
            'final' => TempFile::base64ToImg($this->response['lighthouseResult']['audits']['final-screenshot']['details']['data']),   
        );
    }

    public function isOK(): bool {
        return true; //because it's always true
    }

    public function getSummary() {
        return $this->summary;
    }

    public function __toString() {
        return $this->jsonSerialize();
    }

    protected function createSummary() {
        $this->summary = array(
            'screenshots' => $this->getScreenshots(),
        );
    }
}
