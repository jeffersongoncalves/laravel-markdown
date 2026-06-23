<?php

namespace JeffersonGoncalves\Markdown;

/**
 * Container-resolvable instance wrapper around the static {@see Markdown}
 * engine, so the package can be used through the `Markdown` facade (and be
 * mocked/swapped in tests) without duplicating the rendering logic.
 */
class MarkdownRenderer
{
    /**
     * @param  array<string, mixed>  $options
     */
    public function render(string $markdown, bool $headingPermalinks = false, array $options = []): string
    {
        return Markdown::render($markdown, $headingPermalinks, $options);
    }
}
