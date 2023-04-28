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
     * @example array('example.com' => 'wordpress')
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
        
        $dns = new Dns($domain);

        $dns->execute();


        return 'lol';
    }

}
