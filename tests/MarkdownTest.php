<?php

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
