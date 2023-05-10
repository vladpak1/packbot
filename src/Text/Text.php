<?php

namespace PackBot;

/**
 * Class for text output. Escaping text and translating into the user's chosen language.
 *
 * Usage:
 *  call Text::e('text') to show text in the user's language
 */
class Text
{
    /**
     * User's language.
     */
    protected string $language;

    public function __construct()
    {
        $settings = new UserSettings();

        $this->language = $settings->get('language');
    }

    /**
     * Translates each line and concatenates into one line.
     */
    public function concat($separator, string ...$strings)
    {
        $translated = [];

        foreach ($strings as $string) {
            $translated[] = $this->translate($string);
        }

        return implode($separator, $translated);
    }

    public function concatEOL(string ...$strings)
    {
        return $this->concat(PHP_EOL, ...$strings);
    }

    public function concatDoubleEOL(string ...$strings)
    {
        return $this->concat(PHP_EOL . PHP_EOL, ...$strings);
    }

    public function e(string $text): string
    {
        return '' === $text ? '' : $this->translate($text);
    }

    public function sprintf(string $text, ...$args): string
    {
        return sprintf($this->translate($text), ...$args);
    }

    public function getCurrentLanguage(): string
    {
        return $this->language;
    }

    protected function translate(string $text)
    {
        $translator = new Translator($this->language);

        return $this->replaceSpecialChars($translator->translate($text));
    }

    protected function replaceSpecialChars(string $text)
    {
        $text = str_replace('*', '•', $text);
        $text = str_ireplace('<br>', PHP_EOL, $text);

        return $text;
    }
}
