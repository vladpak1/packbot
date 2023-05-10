<?php

namespace PackBot;

interface ToolInterface
{
    /**
     * Tool constructor.
     * The domains must be checked for validity before passing them to the constructor.
     * @throws ToolException
     */
    public function __construct(array|string $domains);

    /**
     * Get the result of the tool.
     * @return array An array $domain => $result.
     * @example array('example.com' => PageSpeedResponse)
     * @see Concrete tool classes for more details.
     */
    public function getResult(): array;
}
