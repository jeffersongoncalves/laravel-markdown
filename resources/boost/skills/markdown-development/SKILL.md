---
name: markdown-development
description: Development guide for laravel-markdown, a shared CommonMark renderer with GitHub Flavored Markdown, optional heading permalinks, and server-side syntax highlighting (class-based tokens) via tempest/highlight.
---

# Markdown Development Skill

## When to use this skill

- When developing or extending the laravel-markdown package
- When changing how Markdown is converted to HTML (extensions, renderers, priorities)
- When adjusting syntax-highlighting behaviour on fenced code blocks
- When adding or changing configuration options
- When writing tests for the renderer

## Setup

### Requirements
- PHP 8.2+
- Laravel 11, 12, or 13
- `spatie/laravel-package-tools` ^1.14
- `league/commonmark` ^2.4
- `tempest/highlight` ^2.25

### Installation

```bash
composer require jeffersongoncalves/laravel-markdown
```

```bash
php artisan vendor:publish --tag="markdown-config"
```

## Package Structure

```
src/
  MarkdownServiceProvider.php   # Registers the package + config file
  Markdown.php                  # Static render() helper (the whole public API)
config/
  markdown.php                  # html_input, allow_unsafe_links, heading_permalink
```

## Public API

```php
JeffersonGoncalves\Markdown\Markdown::render(
    string $markdown,
    bool $headingPermalinks = false,
): string
```

- Always enabled: `CommonMarkCoreExtension` + `GithubFlavoredMarkdownExtension`.
- `HeadingPermalinkExtension` is added only when `$headingPermalinks` is `true`.
- The block-level `FencedCode` renderer is overridden at **priority 10** (outranks GFM's default) with tempest/highlight's `CodeBlockRenderer` wrapping a `Highlighter` built on `CssTheme`.

## How highlighting works

`CssTheme` emits class-based tokens (`<span class="hl-keyword">`, etc.) rather than inline `style="..."` attributes. This matters because:

- The rendered output is typically run through an HTML sanitizer before display.
- A sanitizer strips inline styles but keeps class attributes, so class-based tokens survive.
- You style the `.hl-*` classes in your own CSS.

Only the **block-level** `FencedCode` renderer is overridden — inline `` `code` `` keeps its plain CommonMark rendering.

## Configuration

`config/markdown.php` is read inside `render()` (with the same hardcoded defaults as fallbacks):

```php
'html_input' => 'allow',            // 'allow' | 'escape' | 'strip'
'allow_unsafe_links' => false,
'heading_permalink' => [
    'symbol' => '#',
    'html_class' => 'md-anchor',
],
```

The `heading_permalink` config is only applied when `render(..., headingPermalinks: true)` is called.

## Security

The default `html_input => allow` means raw HTML in the Markdown source is preserved in the output. **The output is UNSAFE for untrusted input.** Pair this package with an HTML sanitizer (e.g. `jeffersongoncalves/laravel-html-sanitizer`) before displaying third-party READMEs or imported article bodies. Never feed the output into a `{!! !!}` Blade sink without sanitising first.

## Testing Patterns

```php
use JeffersonGoncalves\Markdown\Markdown;

it('renders a GFM table', function () {
    $html = Markdown::render("| A | B |\n| - | - |\n| 1 | 2 |");

    expect($html)->toContain('<table>')->toContain('<td>1</td>');
});

it('highlights fenced code blocks', function () {
    $html = Markdown::render("```php\necho 'hi';\n```");

    expect($html)->toMatch('/class="hl-[a-z]+"/');
});

it('adds md-anchor anchors only when enabled', function () {
    expect(Markdown::render('# H', headingPermalinks: true))->toContain('md-anchor');
    expect(Markdown::render('# H'))->not->toContain('md-anchor');
});
```

### Running Tests

```bash
# Run all tests
vendor/bin/pest

# Run with coverage
vendor/bin/pest --coverage

# Static analysis
vendor/bin/phpstan analyse

# Code formatting
vendor/bin/pint
```

## Extending

- **Add a Markdown extension:** call `$environment->addExtension(new SomeExtension)` in `render()` (gate it behind a config flag or method argument if it is optional).
- **Change highlight theme:** swap `new CssTheme` for another `Tempest\Highlight\Theme` implementation — prefer class-based themes so the output survives sanitisation.
- **Override another node renderer:** call `$environment->addRenderer(NodeClass::class, $renderer, $priority)` with a priority above the default to win.
