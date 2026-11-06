<?php

namespace App\Support;

class HtmlText
{
    public static function plain(?string $value): string
    {
        $text = (string) $value;

        if (trim($text) === '') {
            return '';
        }

        $text = str_replace('[base_url]', url('/') . '/', $text);
        $text = preg_replace('/<br\s*\/?>/i', "\n", $text) ?? $text;
        $text = preg_replace('/<\/(p|div|li|h[1-6]|tr|blockquote)>/i', "\n", $text) ?? $text;
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim($text);
    }
}
