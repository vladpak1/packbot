<?php

namespace PackBot;

class Curl
{
    /**
     * URL to be requested.
     */
    protected string $url;

    /**
     * Response of the request.
     */
    protected CurlResponse $response;

    /**
     * Optional PageSpeedResponse.
     */
    protected PageSpeedResponse $pagespeedResponse;

    /**
     * Determine if we need to add the PageSpeed API.
     */
    protected bool $isLiteSpeed = false;

    /**
     * Default curl options.
     */
    protected array $curlOptions = [
        'CURLOPT_USERAGENT'      => 'PackBot/2.0 (+https://t.me/packhelperbot)',
        'CURLOPT_SSL_VERIFYPEER' => 0,
        'CURLOPT_SSL_VERIFYHOST' => 0,
        'CURLOPT_CONNECTTIMEOUT' => 5,
        'CURLOPT_TIMEOUT'        => 5,
        'CURLOPT_MAXREDIRS'      => 3,
        'CURLOPT_FOLLOWLOCATION' => 1,
        'CURLOPT_RETURNTRANSFER' => 1,
        'CURLOPT_HTTPHEADER'     => [],
    ];

    /**
     * Error message if curl request fails.
     */
    protected string $curlError = '';

    /**
     * Determine if the request is executed.
     */
    protected bool $isExecuted = false;

    /**
     * The main cURL class constructor.
     *
     * Usage:
     *  1. $curl = new Curl('https://example.com');
     *  2. $curl->execute();
     *  3. $curl->getResponse();
     *
     *  If you want to use PageSpeed API, you can use $curl->enableLitespeed() method.
     *
     * @method getCurlError()             Get the error message if curl request fails.
     * @method getResponse()              Get the response of the request.
     * @method isOK()                     Determine if the response is OK.
     * @method setTimeout(int $seconds)   Set timeout in seconds.
     * @method setHeaders(array $headers) Set headers.
     * @method enableLitespeed()          Enable PageSpeed API.
     *
     * @param  string        $url URL to be requested.
     * @throws CurlException
     */
    public function __construct(string $url)
    {
        $this->url = $url;
    }

    /**
     * Execute the curl request.
     * @throws CurlException
     */
    public function execute(): self
    {
        if ($this->isExecuted) {
            throw new CurlException('Cannot execute curl request: curl request is already executed.');
        }
        $this->realExecute();

        return $this;
    }

    /**
     * Enable PageSpeed API.
     * This will include PageSpeedResponse in the CurlResponse.
     */
    public function enableLitespeed(): self
    {
        $this->isLiteSpeed = true;

        return $this;
    }

    /**
     * Get the response of the request.
     * @see CurlResponse for more details.
     */
    public function getResponse(): CurlResponse
    {
        if (!$this->isExecuted) {
            throw new CurlException('Cannot get response: curl request is not executed.');
        }

        return $this->response;
    }

    /**
     * Get the error message if curl request fails.
     * If there is no error, it will return an empty string.
     */
    public function getCurlError(): string
    {
        return $this->curlError;
    }

    /**
     * Determine if the response is OK (200).
     */
    public function isOK(): bool
    {
        return 200 === $this->getResponse()->getCode();
    }

    /**
     * Set cURL timeout in seconds.
     */
    public function setTimeout(int $seconds): self
    {
        $this->curlOptions['CURLOPT_TIMEOUT'] = $seconds;

        return $this;
    }

    /**
     * Set cURL headers.
     * @param array $headers Array of headers.
     * @example array('Content-Type: application/json', 'Content-Length: 100')
     */
    public function setHeaders(array $headers): self
    {
        $this->curlOptions['CURLOPT_HTTPHEADER'] = $headers;

        return $this;
    }

    /**
     * Set cURL follow location.
     * @example true
     */
    public function setFollowLocation(bool $followLocation): self
    {
        $this->curlOptions['CURLOPT_FOLLOWLOCATION'] = $followLocation;

        return $this;
    }

    protected function setPageSpeed(PageSpeedResponse $pageSpeedResponse): self
    {
        $this->response->addPageSpeed($pageSpeedResponse);

        return $this;
    }

    protected function realExecute()
    {
        $ch = curl_init();

        foreach ($this->curlOptions as $option => $value) {
            curl_setopt($ch, constant($option), $value);
        }

        curl_setopt($ch, CURLOPT_URL, $this->url);

        $response = curl_exec($ch);
        $curlInfo = curl_getinfo($ch);
        curl_close($ch);

        if (!$response || !$curlInfo) {
            $this->curlError = curl_error($ch);
        }
        $this->isExecuted = true;
        $this->response   = new CurlResponse([
            'curlInfo'  => $curlInfo,
            'curlError' => $this->curlError,
            'response'  => $response,
        ]);

        try {
            if ($this->isOK() && $this->isLiteSpeed) {
                $pagespeed         = new PageSpeedTool($this->url);
                $pagespeedResponse = $pagespeed->getResult()[$this->url];
                $this->setPageSpeed($pagespeedResponse);
            }
        } catch (ToolException $e) {
            error_log('Trying to create PageSpeed for ' . $this->url . ' failed: ' . $e);
        }
    }
}
