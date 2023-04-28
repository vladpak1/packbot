<?php

namespace PackBot;

class Report extends Text {

    protected string $title = '';

    protected array $blocks = array();

    public function __construct() {
        parent::__construct();
    }


    public function setTitle(string $title): Report {
        $this->title = '<b>' . $this->e($title) . '</b>';

        return $this;
    }

    public function addBlock(string|array $text): Report {
        if (is_array($text)) {
            $this->blocks[] = $this->concat('', ...$text);
        } else {
            $this->blocks[] = $this->e($text);
        }

        return $this;
    }

    public function addBlocks(array $blocks): Report {
        foreach ($blocks as $block) {
            $this->addBlock($block);
        }

        return $this;
    }

    public function getReport(): string {
        $report = $this->title . PHP_EOL . PHP_EOL;
        foreach ($this->blocks as $block) {
            $report .= $block . PHP_EOL . PHP_EOL;
        }

        return $report;
    }

}
