<div class="filament-hidden">

![Laravel Markdown](https://raw.githubusercontent.com/jeffersongoncalves/laravel-markdown/master/art/jeffersongoncalves-laravel-markdown.png)

</div>

# Laravel Markdown

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jeffersongoncalves/laravel-markdown.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/laravel-markdown)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/jeffersongoncalves/laravel-markdown/run-tests.yml?branch=master&label=tests&style=flat-square)](https://github.com/jeffersongoncalves/laravel-markdown/actions?query=workflow%3Arun-tests+branch%3Amaster)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/jeffersongoncalves/laravel-markdown/fix-php-code-style-issues.yml?branch=master&label=code%20style&style=flat-square)](https://github.com/jeffersongoncalves/laravel-markdown/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/jeffersongoncalves/laravel-markdown.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/laravel-markdown)

A shared CommonMark renderer for Laravel with GitHub Flavored Markdown, optional heading permalinks, and server-side syntax highlighting on fenced code blocks. Highlighting is class-based (`<span class="hl-…">` tokens via [tempest/highlight](https://github.com/tempestphp/highlight)'s `CssTheme`) so the markup survives HTML sanitisation — you style the `.hl-*` classes in your own CSS.

## Installation

You can install the package via composer:

```bash
composer require jeffersongoncalves/laravel-markdown
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="markdown-config"
```

This is the contents of the published config file:

```php
return [
    'html_input' => 'allow',
    'allow_unsafe_links' => false,
    'heading_permalink' => [
        'symbol' => '#',
        'html_class' => 'md-anchor',
    ],
];
```

## Usage

```php
use JeffersonGoncalves\Markdown\Markdown;

// Render GitHub Flavored Markdown to HTML
$html = Markdown::render('# Hello **world**');

// Enable heading permalink anchors (adds <a class="md-anchor"> to each heading)
$html = Markdown::render($readme, headingPermalinks: true);
```

Fenced code blocks are highlighted server-side and emit class-based tokens:

````php
$html = Markdown::render(<<<'MD'
```php
echo 'hello';
```
MD);
// => <pre><code>…<span class="hl-keyword">echo</span>…</code></pre>
````

Add the matching `.hl-*` styles (and `.md-anchor` if you use heading permalinks) to your own CSS.

> [!WARNING]
> The renderer runs with `html_input` set to `allow`, so **the output is UNSAFE** for untrusted input (third-party READMEs, imported article bodies): raw HTML in the source is preserved. Always pass the output through an HTML sanitizer such as [jeffersongoncalves/laravel-html-sanitizer](https://github.com/jeffersongoncalves/laravel-html-sanitizer) before displaying it. Class-based highlight tokens are designed to survive sanitisation; inline-style highlighting would not.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Jèfferson Gonçalves](https://github.com/jeffersongoncalves)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
