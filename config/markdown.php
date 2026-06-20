<?php

return [

    /*
    |--------------------------------------------------------------------------
    | HTML Input Handling
    |--------------------------------------------------------------------------
    |
    | Controls how raw, inline HTML inside the markdown source is handled.
    | One of: 'allow', 'escape', 'strip'. The default is 'allow' so that
    | trusted authoring (READMEs, imported article bodies) keeps its HTML —
    | this means the OUTPUT IS UNSAFE for untrusted input and MUST be passed
    | through an HTML sanitizer (e.g. jeffersongoncalves/laravel-html-sanitizer)
    | before display.
    |
    */

    'html_input' => 'allow',

    /*
    |--------------------------------------------------------------------------
    | Allow Unsafe Links
    |--------------------------------------------------------------------------
    |
    | When false, CommonMark strips potentially unsafe link protocols such as
    | javascript:, vbscript:, file: and data: from links and images.
    |
    */

    'allow_unsafe_links' => false,

    /*
    |--------------------------------------------------------------------------
    | Heading Permalinks
    |--------------------------------------------------------------------------
    |
    | Configuration applied to the HeadingPermalink extension when it is
    | enabled (Markdown::render($markdown, headingPermalinks: true)). The
    | symbol is rendered inside each anchor and html_class is added to it.
    |
    */

    'heading_permalink' => [
        'symbol' => '#',
        'html_class' => 'md-anchor',
    ],

];
