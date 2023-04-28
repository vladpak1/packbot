<?php

namespace PackBot;

class CurlResponse extends Entity implements \JsonSerializable {

    protected array $response;

    protected bool $isOK;

    protected string $curlError;

    /**
     * CurlResponse is a class that represents the response of the curl request.
     * The response can be converted to json.
     * 
     * @method bool isOK() Check if the response is OK.
     * @method object getRawData() Get the data as an object.
     * @method int getCode() Get the response code.
     * @method string getBody() Get the response body.
     * @method string getIP() Get the IP address of the server.
     * @method string jsonSerialize() Serialize to json all properties of the class.
     * @method string __toString() Converting to string.
     * @method string getCurlError() Get the curl error.
     * @method string getContentType() Get the content type.
     * @method string getRedirectUrl() Get the redirect url.
     * @method int getRedirectCount() Get the redirect count.
     * @method string getEffectiveUrl() Get the effective url.
     * @method int getDnsLookupTime() Get the DNS lookup time.
     * @method int getDownloadSize() Get the download size.
     * @method PageSpeedResponse getPageSpeed() Get the PageSpeed response.
     * 
     * @param array $response The response of the curl request.
     */
    public function __construct(array $response) {
        $this->response = $response;
        $this->isOK = $this->response['curlInfo']['http_code'] === 200;
        $this->curlError = $this->response['curlError'];
    }

    public function jsonSerialize(): string {
        return json_encode($this->response);
    }

    public function __toString() {
        return $this->jsonSerialize();
    }

    public function isOK(): bool {
        return $this->isOK;
    }

    public function getRawData(): object {
        return (object) $this->response;
    }

    public function getCode(): int {
        return $this->response['curlInfo']['http_code'];
    }

    public function getBody(): string {
        return $this->response['response'];
    }

    public function getIP(): string {
        return $this->response['curlInfo']['primary_ip'];
    }

    public function getContentType(): string {
        return $this->response['curlInfo']['content_type'] ?? 'unknown';
    }

    public function getRedirectUrl(): string {
        return $this->response['curlInfo']['redirect_url'];
    }

    public function getRedirectCount(): int {
        return $this->response['curlInfo']['redirect_count'];
    }

    public function addPageSpeed(PageSpeedResponse $pageSpeedResponse): self {
        $this->response['pageSpeed'] = $pageSpeedResponse;
        return $this;
    }

    public function getPageSpeed(): PageSpeedResponse {
        return $this->response['pageSpeed'];
    }

    /**
     * @return int Time in milliseconds it took from the start until the SSL connect/handshake to the remote host was completed.
     */
    public function getDnsLookupTime(): int {
        return $this->response['curlInfo']['namelookup_time'] * 1000;
    }

    public function getEffectiveUrl(): string {
        return $this->response['curlInfo']['url'];
    }

    public function getDownloadSize(): int {
        return $this->response['curlInfo']['size_download'];
    }

    public function getUploadSize(): int {
        return $this->response['curlInfo']['size_upload'];
    }

    /**
     * @return int Average download speed in mb per second.
     */
    public function getSpeedDownload(): int {
        return $this->response['curlInfo']['speed_download'] / 1024 / 1024;
    }

    public function getSpeedUpload(): int {
        return $this->response['curlInfo']['speed_upload'];
    }

    public function getDownloadContentLength(): int {
        return $this->response['curlInfo']['download_content_length'];
    }

    public function getUploadContentLength(): int {
        return $this->response['curlInfo']['upload_content_length'];
    }

    public function getStartTransferTime(): int {
        return $this->response['curlInfo']['starttransfer_time'];
    }
    
    /**
     * @return int Total time of the previous transfer in milliseconds.
     */
    public function getTotalTime(): int {
        return $this->response['curlInfo']['total_time'] * 1000;
    }

    public function getNamelookupTime(): int {
        return $this->response['curlInfo']['namelookup_time'];
    }

    public function getResponse(): string {
        return $this->response['response'];
    }

    public function getCurlError(): string {
        $this->tryToDetermineError();
        return $this->curlError;
    }

    protected function tryToDetermineError() {
        if (!empty($this->curlError)) return;
        if ($this->getCode() != 200) $this->curlError = 'HTTP code is not 200, it is ' . $this->getCode() . ' ('. HttpDescription::getCodeDescription($this->getCode(), 'en_US') .').';
    }
}
