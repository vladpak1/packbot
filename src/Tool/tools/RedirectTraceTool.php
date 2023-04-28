<?php

namespace PackBot;

class RedirectTraceTool implements ToolInterface {

    protected array $domains = array();

    protected Text $text;

    protected array $result;

    protected array $toolSettings;

    protected string $toolName = 'RedirectTraceTool';

    public function __construct(array|string $domains) {
        if (is_string($domains)) $domains = array($domains);

        $this->text = new Text();

        $this->toolSettings = Environment::var('tools_settings')[$this->toolName];
        if ($this->toolSettings['enabled'] === false) throw new ToolException($this->toolName . ' is temporarily disabled.');


        $this->domains = $domains;
        $this->prepareResults();
    }

    public function getResult(): array {
        return $this->result;
    }

    protected function prepareResults() {
        foreach ($this->domains as $domain) {
            $this->result[$domain] = $this->executeTracer($domain);
        }
    }

    protected function executeTracer(string $domain): array {
        try {
            $tracer = new RedirectTracer($domain);
            $redirectHistory = $tracer->getRedirectHistory();

            if (count($redirectHistory) > $this->toolSettings['maxRedirects']) {
                throw new ToolException('Too many redirects for ' . $domain . '.');
            }

            return $redirectHistory;
        } catch (RedirectTracerException $e) {
            throw new ToolException('Cannot trace redirects for ' . $domain . ': ' . $e->getMessage());
        }

    }
}
