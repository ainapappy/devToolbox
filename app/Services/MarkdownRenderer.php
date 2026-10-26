<?php

namespace App\Services;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\MarkdownConverter;

/**
 * Rendu Markdown → HTML sécurisé (échappement du HTML brut).
 */
final class MarkdownRenderer
{
    private static ?MarkdownConverter $converter = null;

    public static function toHtml(string $markdown): string
    {
        return self::converter()->convert($markdown)->getContent();
    }

    private static function converter(): MarkdownConverter
    {
        if (self::$converter !== null) {
            return self::$converter;
        }

        $environment = new Environment([
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 20,
        ]);

        $environment->addExtension(new CommonMarkCoreExtension);
        $environment->addExtension(new AutolinkExtension);
        $environment->addExtension(new StrikethroughExtension);
        $environment->addExtension(new TableExtension);

        self::$converter = new MarkdownConverter($environment);

        return self::$converter;
    }
}
