<?php

namespace JeffersonGoncalves\Markdown\Facades;

use Illuminate\Support\Facades\Facade;
use JeffersonGoncalves\Markdown\MarkdownRenderer;

/**
 * @method static string render(string $markdown, bool $headingPermalinks = false, array $options = [])
 *
 * @see MarkdownRenderer
 */
class Markdown extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return MarkdownRenderer::class;
    }
}
