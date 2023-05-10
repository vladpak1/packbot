<?php

namespace PackBot;

class SitemapParserTool implements ToolInterface
{
    protected array $domains = [];

    protected Text $text;

    protected array $result;

    protected array $toolSettings;

    protected string $toolName = 'SitemapParserTool';

    public function __construct(array|string $domains)
    {
        if (is_string($domains)) {
            $domains = [$domains];
        }

        $this->text = new Text();

        $this->toolSettings = Environment::var('tools_settings')[$this->toolName];

        if (false === $this->toolSettings['enabled']) {
            throw new ToolException($this->toolName . ' is temporarily disabled.');
        }

        $this->domains = $domains;
        $this->prepareResults();
    }

    public function getResult(): array
    {
        return $this->result;
    }

    protected function prepareResults()
    {
        foreach ($this->domains as $domain) {
            $this->result[$domain] = $this->executeParser($domain);
        }
    }

    protected function executeParser(string $domain): array
    {
        try {
            $sitemapParser = new SitemapParser($domain);
            $response      = $sitemapParser
                                    ->setMaxSitemapsAtOnce($this->toolSettings['maxSitemapsAtOnce'])
                                    ->setTimeLimit($this->toolSettings['timeLimit'])
                                    ->setSitemapCurlWaitTime($this->toolSettings['sitemapCurlWaitTime'])
                                    ->execute()
                                    ->getLinks();

            return $response;
        } catch (SitemapParserException $e) {
            throw new ToolException('Cannot parse sitemap for ' . $domain . ': ' . $e->getMessage());
        }
    }
}
