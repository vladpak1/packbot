<?php

namespace PackBot;

class DnsTool implements ToolInterface {

    protected array $domains = array();

    protected Text $text;

    protected array $result = array();

    protected array $toolSettings;

    protected string $toolName = 'DnsTool';

    public function __construct(array|string $domains) {
        if (is_string($domains)) $domains = array($domains);

        $this->text = new Text();

        $this->toolSettings = Environment::var('tools_settings')[$this->toolName];
        if ($this->toolSettings['enabled'] === false) throw new ToolException('Dns tool is temporarily disabled.');


        $this->domains = $domains;
        $this->prepareResults();
    }

    /**
     * Get the result of the dns lookup.
     * @example array(...records)
     * @see DnsRecord for the structure of the records.
     */
    public function getResult(): array {
        return $this->result;
    }

    protected function prepareResults() {
        foreach ($this->domains as $domain) {
            $this->result[$domain] = $this->executeDns($domain);
        }
    }

    protected function executeDns($domain) {
        try {
            $dns     = new Dns($domain);
            $records = $dns->execute()->getRecords();
        } catch (DnsException $e) {
            throw new ToolException(sprintf('Error while executing dns lookup for domain %s. Error: %s', $domain, $e->getMessage()));
        }

        if (count($records) === 0) throw new ToolException(sprintf('No dns records found for domain %s.', $domain));
        Debug::toConsole($records);
        return $records;
    }

}
