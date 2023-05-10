<?php

namespace PackBot;

class IndexPossibilityCheckTool implements ToolInterface
{
    protected array $domains = [];

    protected Text $text;

    protected array $result = [];

    protected array $toolSettings;

    public function __construct(array|string $domains)
    {
        if (is_string($domains)) {
            $domains = [$domains];
        }

        $this->text = new Text();

        $this->toolSettings = Environment::var('tools_settings')['IndexPossibilityCheckTool'];

        if (false === $this->toolSettings['enabled']) {
            throw new ToolException('Index possibility check tool is temporarily disabled.');
        }

        $this->domains = $domains;
        $this->prepareResults();
    }

    /**
     * Get the result of the index possibility check.
     * @example array('example.com' => IndexPossibilityResponse)
     * @see IndexPossibilityResponse for more details.
     */
    public function getResult(): array
    {
        return $this->result;
    }

    protected function prepareResults()
    {
        foreach ($this->domains as $domain) {
            $this->result[$domain] = $this->executeIndexPossibilityCheck($domain);
        }
    }

    protected function executeIndexPossibilityCheck(string $domain): IndexPossibilityResponse
    {
        try {
            $indexPossibility = new IndexPossibility($domain);

            return $indexPossibility
                    ->execute()
                    ->getResponse();

        } catch (IndexPossibilityException $e) {
            error_log('Cannot get index possibility info for ' . $domain . '. ' . $e->getMessage());

            throw new ToolException('Cannot get index possibility info for ' . $domain . ': ' . $e->getMessage());
        }
    }
}
