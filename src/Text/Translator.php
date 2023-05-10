<?php

namespace PackBot;

/**
 * A class for language management.
 */
class Translator
{
    protected string $languageCode;

    protected string $originLanguageCode = 'ru_RU';

    public function __construct(string $languageCode)
    {
        $this->languageCode = $languageCode;
    }

    public function translate(string $text): string
    {
        if ($this->languageCode == $this->originLanguageCode) {
            return $text;
        }
        $language = $this->getLanguage();

        if (!isset($language[$text])) {
            return $text;
        }

        return $language[$text];
    }

    public function getLanguage(): array
    {
        $languageDir = Path::toLanguages();

        if (!file_exists($languageDir)) {
            throw new LanguageException('Language directory does not exist');
        }

        if (!file_exists($languageDir . '/' . $this->languageCode . '.txt')) {
            throw new LanguageException('Language file ' . $this->languageCode . '.txt does not exist');
        }

        $dictionary = $this->txtToLanguage($languageDir . '/' . $this->languageCode . '.txt');

        if (!$dictionary) {
            throw new LanguageException('Language file ' . $this->languageCode . '.txt is empty or invalid');
        }

        return $dictionary;

    }

    protected function txtToLanguage(string $path): array|false
    {
        $file = file_get_contents($path);

        if (false === $file) {
            return false;
        }

        $lines = explode("\n", $file);

        if (count($lines) < 1) {
            return false;
        }

        $dictionary = [];
        $i          = 1;

        foreach ($lines as $line) {
            $line    = trim($line);
            $phrases = explode(' = ', $line);

            if (!$phrases || count($phrases) < 2) {
                error_log('Invalid line in language file "' . $path . '": ' . $line . ' (line ' . $i . ')');

                continue;
            }
            $dictionary[$phrases[0]] = $phrases[1];

            $i++;
        }

        return $dictionary;
    }
}
