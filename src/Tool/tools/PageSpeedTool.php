<?php

namespace PackBot;

class PageSpeedTool implements ToolInterface
{
    protected array $domains = [];

    protected Text $text;

    protected array $result;

    protected array $toolSettings;

    protected string $toolName = 'PageSpeedTool';

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

    /**
     * Get the result of the PageSpeed.
     * @example array('example.com' => PageSpeedResponse)
     * @see PageSpeedResponse for more details.
     */
    public function getResult(): array
    {
        return $this->result;
    }

    protected function prepareResults()
    {
        foreach ($this->domains as $domain) {
            $this->result[$domain] = $this->executePageSpeed($domain);
        }
    }

    protected function executePageSpeed($domain): PageSpeedResponse
    {
        try {
            $pagespeed = new PageSpeed($domain);

            return $pagespeed
                    ->execute()
                    ->getResponse();

        } catch (PageSpeedException $e) {
            error_log('Cannot get PageSpeed info for ' . $domain . '. ' . $e->getMessage());

            throw new ToolException('Cannot get PageSpeed info for ' . $domain . ': ' . $e->getMessage());
        }

    }
}
