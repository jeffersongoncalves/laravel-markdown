<?php

use Illuminate\Support\Facades\Blade;
use JeffersonGoncalves\Markdown\Facades\Markdown as MarkdownFacade;
use JeffersonGoncalves\Markdown\Markdown;

it('renders a heading', function () {
    $html = Markdown::render('# Hello World');

    expect($html)->toContain('<h1>')
        ->toContain('Hello World')
        ->toContain('</h1>');
});

it('renders an unordered list', function () {
    $html = Markdown::render("- one\n- two\n- three");

    expect($html)->toContain('<ul>')
        ->toContain('<li>one</li>')
        ->toContain('<li>two</li>')
        ->toContain('<li>three</li>');
});

it('renders a GitHub Flavored Markdown table', function () {
    $markdown = <<<'MD'
    | Name | Role |
    | ---- | ---- |
    | Jeff | Dev  |
    MD;

    $html = Markdown::render($markdown);

    expect($html)->toContain('<table>')
        ->toContain('<thead>')
        ->toContain('<th>Name</th>')
        ->toContain('<th>Role</th>')
        ->toContain('<td>Jeff</td>')
        ->toContain('<td>Dev</td>');
});

it('renders GitHub Flavored Markdown strikethrough', function () {
    $html = Markdown::render('~~gone~~');

    expect($html)->toContain('<del>gone</del>');
});

it('highlights fenced code blocks with hl-* span classes', function () {
    $markdown = <<<'MD'
    ```php
    echo 'hello';
    ```
    MD;

    $html = Markdown::render($markdown);

    expect($html)->toContain('<pre')
        ->toMatch('/class="hl-[a-z]+"/');
});

it('adds md-anchor permalink anchors to headings when enabled', function () {
    $html = Markdown::render('# Hello World', headingPermalinks: true);

    expect($html)->toContain('md-anchor');
});

it('does not add permalink anchors to headings by default', function () {
    $html = Markdown::render('# Hello World');

    expect($html)->not->toContain('md-anchor');
});

it('honours a custom heading permalink html class from config', function () {
    config()->set('markdown.heading_permalink.html_class', 'custom-anchor');

    $html = Markdown::render('# Hello World', headingPermalinks: true);

    expect($html)->toContain('custom-anchor')
        ->not->toContain('md-anchor');
});

it('escapes raw HTML by default (safe out of the box)', function () {
    expect(config('markdown.html_input'))->toBe('escape');

    $html = Markdown::render('<script>alert(1)</script>'."\n\n".'<div>hi</div>');

    expect($html)
        ->not->toContain('<script>')
        ->not->toContain('<div>')
        ->toContain('&lt;script&gt;')
        ->toContain('&lt;div&gt;');
});

it('passes raw HTML through when html_input is allow', function () {
    config()->set('markdown.html_input', 'allow');

    $html = Markdown::render('<div>hi</div>'."\n\n".'<span>x</span>');

    expect($html)
        ->toContain('<div>hi</div>')
        ->toContain('<span>x</span>');
});

it('still neutralises <script> even when html_input is allow (GFM tag filter)', function () {
    config()->set('markdown.html_input', 'allow');

    $html = Markdown::render('<script>alert(1)</script>');

    // GitHub Flavored Markdown's disallowed-raw-html filter escapes <script>
    // (and other dangerous tags) even when raw HTML is otherwise allowed.
    expect($html)
        ->not->toContain('<script>')
        ->toContain('&lt;script>alert(1)');
});

it('removes raw HTML when html_input is strip', function () {
    config()->set('markdown.html_input', 'strip');

    $html = Markdown::render('<script>alert(1)</script>'."\n\n".'<div>hi</div>');

    expect($html)
        ->not->toContain('<script>')
        ->not->toContain('<div>')
        ->not->toContain('&lt;script&gt;');
});

it('neutralises javascript: links when allow_unsafe_links is false (the default)', function () {
    expect(config('markdown.allow_unsafe_links'))->toBeFalse();

    $html = Markdown::render('[x](javascript:alert(1))');

    expect($html)
        ->not->toContain('javascript:')
        ->toContain('x');
});

it('preserves unsafe links when allow_unsafe_links is true', function () {
    config()->set('markdown.allow_unsafe_links', true);

    $html = Markdown::render('[x](javascript:alert(1))');

    expect($html)->toContain('javascript:alert(1)');
});

it('neutralises data:, vbscript: and file: links by default', function () {
    expect(Markdown::render('[a](data:text/html,x)'))->not->toContain('data:text/html');
    expect(Markdown::render('[b](vbscript:msgbox(1))'))->not->toContain('vbscript:');
    expect(Markdown::render('[c](file:///etc/passwd)'))->not->toContain('file:///');
});

it('honours a custom heading permalink symbol from config', function () {
    config()->set('markdown.heading_permalink.symbol', '¶');

    $html = Markdown::render('# Hello', headingPermalinks: true);

    expect($html)->toContain('¶');
});

it('renders inline code as a plain code element without highlighting', function () {
    $html = Markdown::render('Use `array_map` here.');

    expect($html)
        ->toContain('<code>array_map</code>')
        ->not->toMatch('/class="hl-/');
});

it('rebuilds the converter after flush so a config change takes effect mid-process', function () {
    // Warm the memoized 'default' converter with the escape default.
    Markdown::render('# warm up');

    config()->set('markdown.html_input', 'allow');
    Markdown::flush();

    expect(Markdown::render('<div>hi</div>'))->toContain('<div>hi</div>');
});

it('overrides html_input per call via options without mutating config', function () {
    expect(config('markdown.html_input'))->toBe('escape');

    $allowed = Markdown::render('<div>hi</div>', options: ['html_input' => 'allow']);
    expect($allowed)->toContain('<div>hi</div>');

    // The config default is untouched, so a plain call still escapes.
    expect(Markdown::render('<div>hi</div>'))->toContain('&lt;div&gt;');
});

it('overrides the heading permalink symbol per call via options', function () {
    $html = Markdown::render('# Hello', headingPermalinks: true, options: [
        'heading_permalink' => ['symbol' => '★'],
    ]);

    expect($html)->toContain('★');
});

it('renders through the Markdown facade', function () {
    expect(MarkdownFacade::render('# Hello'))
        ->toContain('<h1>')
        ->toContain('Hello');
});

it('renders markdown through the @markdown Blade directive', function () {
    expect(Blade::render('@markdown("# Hello")'))
        ->toContain('<h1>')
        ->toContain('Hello');
});

it('passes the heading-permalinks flag through the @markdown Blade directive', function () {
    expect(Blade::render('@markdown("# Hello", true)'))->toContain('md-anchor');
});
