<?php

namespace PackBot;


class PageSpeed {


    protected string $url;

    protected string $baseApiUrl = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed';

    protected ?string $apiKey;

    protected Text $text;

    protected bool $isExecuted = false;

    protected PageSpeedResponse $result;

    protected string $strategy = 'desktop';

    public function __construct(string $url) {
        $this->url = $url;
        $apiKey = Environment::var('tools_settings')['PageSpeedTool']['apiKey'];

        if (empty($apiKey) || $apiKey == 'api_key') {
            $this->apiKey = false;
        } else {
            $this->apiKey = $apiKey;
        }

        $this->text = new Text();
    }

    public function execute(): self {
        if (empty($this->url)) throw new PageSpeedException('URL is not set.');
        if ($this->isExecuted) return $this;

        $this->realExecute();
        return $this;
    }

    public function setStrategy(string $strategy): self {
        if ($this->isExecuted) throw new PageSpeedException('Cannot set strategy after execution.');
        $this->strategy = $strategy;
        return $this;
    }

    public function getResponse(): PageSpeedResponse {
        return $this->result;
    }

    protected function realExecute() {
        $url = $this->generateApiUrl();

        $curl = new Curl($url);
        
        $result = $curl
                    ->setTimeout(120)
                    ->setHeaders(array(
                        'Accept: application/json',
                        'Content-Type: application/json',
                    ))
                    ->execute();

        if (!$curl->isOK()) throw new PageSpeedException('Cannot get PageSpeed info for ' . $this->url . '. Error: ' . $curl->getCurlError());

        $this->result = new PageSpeedResponse($curl->getResponse()->getBody());
        $this->isExecuted = true;
    }

    protected function generateApiUrl() {
        $locale   = $this->text->getCurrentLanguage();
        $key      = $this->apiKey;
        $url      = $this->prepareUrlForPageSpeed($this->url);
        $strategy = $this->strategy;

        if (!$key) return "https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=$url&locale=$locale&strategy=$strategy";
        return "https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=$url&key=$key&locale=$locale&strategy=$strategy";

    }

    protected function prepareUrlForPageSpeed(string $url): string {
        /**
         * Redirects greatly distort pagespeed results.
         * Therefore, we will get an effective url ourselves and pass the final page to pagespeed.
         */
        try {
            $curl = new Curl($url);
            return (string)$curl
                            ->execute()
                            ->getResponse()
                            ->getEffectiveUrl();

        } catch (CurlException $e) {
            error_log('Cannot get smart url!!!');
            return $url;
        }
    }

}