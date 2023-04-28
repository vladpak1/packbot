<?php

namespace PackBot;

class ServerResponseCheckTool implements ToolInterface {

    protected array $domains = array();

    protected Text $text;

    protected array $result;

    protected array $toolSettings;

    protected string $toolName = 'ServerResponseCheckTool';

    public function __construct(array|string $domains) {
        if (is_string($domains)) $domains = array($domains);

        $this->text = new Text();

        $this->toolSettings = Environment::var('tools_settings')['ServerResponseCheckTool'];
        if ($this->toolSettings['enabled'] === false) throw new ToolException($this->toolName . ' is temporarily disabled.');


        $this->domains = $domains;
        $this->prepareResults();
    }

    /**
     * Get the result of the ServerResponseCheck.
     * @example array('example.com' => CurlResponse)
     */
    public function getResult(): array {
        return $this->result;
    }

    protected function prepareResults() {
        foreach ($this->domains as $domain) {
            $url = $domain;
            $response = $this->getServerResponse($url);
            $this->result[$domain] = $response;
        }
    }

    protected function getServerResponse(string $url): CurlResponse {
        $curl = new Curl($url);
        
        return $curl
                    ->enableLitespeed()
                    ->execute()
                    ->getResponse();
    }
}
