<?php

namespace JeffersonGoncalves\Markdown;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\MarkdownConverter;
use Tempest\Highlight\CommonMark\CodeBlockRenderer;
use Tempest\Highlight\Highlighter;
use Tempest\Highlight\Themes\CssTheme;

/**
 * Shared CommonMark renderer for the two markdown surfaces — GitHub READMEs
 * and imported article bodies. By default raw HTML in the source is escaped
 * (config `markdown.html_input` = 'escape'), so the output is safe for
 * untrusted input out of the box. Set it to 'allow' for fully trusted content
 * only, in which case you should run the output through an HTML sanitizer such
 * as jeffersongoncalves/laravel-html-sanitizer before display. Fenced code
 * blocks get server-side syntax highlighting via tempest/highlight's CssTheme:
 * it emits class-based `<span class="hl-…">` tokens (which you style in your
 * own CSS) that survive sanitisation, rather than inline styles which a
 * sanitiser would strip.
 *
 * Only the block-level FencedCode renderer is overridden — inline `code` keeps
 * its plain rendering.
 *
 * The configured MarkdownConverter is memoized per headingPermalinks flag and a
 * single Highlighter instance is reused across renders, so the Environment,
 * extensions, highlighter and converter are built at most once per flag.
 */
class Markdown
{
    /** @var array<string, MarkdownConverter> */
    private static array $converters = [];

    private static ?Highlighter $highlighter = null;

    public static function render(string $markdown, bool $headingPermalinks = false): string
    {
        return self::converter($headingPermalinks)->convert($markdown)->getContent();
    }

    /**
     * Drop the memoized converters and highlighter. Useful when the markdown
     * configuration changes at runtime (e.g. in tests).
     */
    public static function flush(): void
    {
        self::$converters = [];
        self::$highlighter = null;
    }

    private static function converter(bool $headingPermalinks): MarkdownConverter
    {
        $key = $headingPermalinks ? 'permalinks' : 'default';

        return self::$converters[$key] ??= self::makeConverter($headingPermalinks);
    }

    private static function makeConverter(bool $headingPermalinks): MarkdownConverter
    {
        $config = [
            'html_input' => config('markdown.html_input', 'escape'),
            'allow_unsafe_links' => config('markdown.allow_unsafe_links', false),
        ];

        if ($headingPermalinks) {
            $config['heading_permalink'] = [
                'symbol' => config('markdown.heading_permalink.symbol', '#'),
                'html_class' => config('markdown.heading_permalink.html_class', 'md-anchor'),
            ];
        }

        $environment = new Environment($config);
        $environment->addExtension(new CommonMarkCoreExtension);
        $environment->addExtension(new GithubFlavoredMarkdownExtension);

        if ($headingPermalinks) {
            $environment->addExtension(new HeadingPermalinkExtension);
        }

        // Priority 10 outranks GFM's default fenced-code renderer.
        $environment->addRenderer(
            FencedCode::class,
            new CodeBlockRenderer(self::highlighter()),
            10,
        );

        return new MarkdownConverter($environment);
    }

    private static function highlighter(): Highlighter
    {
        return self::$highlighter ??= new Highlighter(new CssTheme);
    }
}
