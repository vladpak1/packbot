<?php

namespace PackBot;

use RobotsTxtParser\RobotsTxtParser;
use RobotsTxtParser\RobotsTxtValidator;
use KubAT\PhpSimple\HtmlDomParser;
use Throwable;

class IndexPossibility
{
    protected string $domain;

    /**
     * Effective URL after redirects.
     */
    protected string $effectiveUrl;

    protected bool $isExecuted = false;

    /**
     * Robots.txt content.
     */
    protected string $robots = '';

    /**
     * Default useragent that'll be used for robots.txt parsing.
     */
    protected string $useragent = '*';

    protected IndexPossibilityResponse $response;

    /**
     * Class constructor.
     */
    public function __construct(string $domain)
    {
        $this->domain = $domain;
    }

    /**
     * Execute the check.
     */
    public function execute(): self
    {
        if ($this->isExecuted) {
            return $this;
        }
        $this->getEffectiveUrl();
        $this->getRobotsTxt();
        $this->realExecute();
        $this->isExecuted = true;

        return $this;
    }

    /**
     * Set useragent for robots.txt parsing.
     */
    public function actAs(string $useragent = '*'): self
    {
        $this->useragent = $useragent;

        return $this;
    }

    /**
     * Get response as IndexPossibilityResponse object.
     */
    public function getResponse(): IndexPossibilityResponse
    {
        if (!$this->isExecuted) {
            throw new IndexPossibilityException('Cannot get response before execution.');
        }

        return $this->response;
    }

    protected function realExecute()
    {
        $parsedRobots   = $this->parseRobotsTxt();
        $this->response = new IndexPossibilityResponse(array_merge($parsedRobots, [
            'effectiveUrl' => $this->effectiveUrl,
        ]));
    }

    protected function parseRobotsTxt(): array
    {
        try {
            $parser    = new RobotsTxtParser($this->robots);
            $validator = new RobotsTxtValidator($parser->getRules());

            return [
                // 'rules' => $parser->getRules(),
                'indexBlockByRobots' => $validator->isUrlDisallow($this->effectiveUrl, $this->useragent),
                'indexBlockByPage'   => $this->isThereNoIndex(),
            ];

        } catch (Throwable $e) {
            throw new IndexPossibilityException('Cannot parse robots.txt: ' . $e->getMessage());
        }
    }

    /**
     * Check if page has noindex meta tag.
     */
    protected function isThereNoIndex(): bool
    {
        $curl     = new Curl($this->effectiveUrl);
        $response = $curl
                        ->setTimeout(20)
                        ->execute()
                        ->getResponse();

        if (!$response->isOK() && 404 != $response->getCode()) {
            throw new IndexPossibilityException('Cannot get page (during in-page check) for ' . $this->effectiveUrl . ': ' . $response->getCurlError());
        }

        if (404 == $response->getCode()) {
            return true;
        }

        $dom = HtmlDomParser::str_get_html($response->getBody());

        $noindex = $dom->find('meta[name="robots"]', 0);

        if (empty($noindex)) {
            return false;
        }
        $content = $noindex->content;

        if (empty($content)) {
            return false;
        }

        return str_contains($content, 'noindex') ? true : false;
    }

    protected function getRobotsTxt()
    {
        $robotsTxtUrl = Url::getDomain($this->effectiveUrl) . '/robots.txt';
        $curl         = new Curl($robotsTxtUrl);
        $response     = $curl
                        ->setTimeout(20)
                        ->execute()
                        ->getResponse();

        if ($response->isOK()) {
            $this->robots = $response->getBody();
        }
    }

    protected function getEffectiveUrl()
    {
        try {
            $curl     = new Curl($this->domain);
            $response = $curl
                            ->setFollowLocation(true)
                            ->setTimeout(20)
                            ->execute()
                            ->getResponse();

            if (!$response->isOK() && 404 != $response->getCode()) {
                throw new IndexPossibilityException('Cannot get effective URL for (1) ' . $this->domain . ': ' . $response->getCurlError());
            }
            $this->effectiveUrl = $response->getEffectiveUrl();
        } catch (CurlException $e) {
            throw new IndexPossibilityException('Cannot get effective URL for (2) ' . $this->domain . ': ' . $e->getMessage());
        }
    }
}
