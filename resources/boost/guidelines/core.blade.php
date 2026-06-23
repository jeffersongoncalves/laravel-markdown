## Laravel Markdown

### Overview
Laravel Markdown is a shared CommonMark renderer. It converts Markdown to HTML with GitHub Flavored Markdown, optional heading permalinks, and server-side syntax highlighting on fenced code blocks via tempest/highlight's class-based `CssTheme`.

**Namespace:** `JeffersonGoncalves\Markdown`
**Service Provider:** `MarkdownServiceProvider` (auto-discovered)
**Main class:** `JeffersonGoncalves\Markdown\Markdown`

### Key Concepts
- **Static API:** `Markdown::render(string $markdown, bool $headingPermalinks = false, array $options = []): string`. Also available via the `Markdown` facade and the `@markdown` Blade directive.
- **GitHub Flavored Markdown:** Tables, task lists, strikethrough, autolinks are enabled.
- **Syntax highlighting:** Only the block-level `FencedCode` renderer is overridden (priority 10) — it emits `<span class="hl-…">` tokens. Inline `code` keeps plain rendering.
- **Safe by default:** Renders with `html_input => escape`, so raw HTML in the source is escaped to visible text. Opt in to `html_input => allow` for trusted content only — then the output is UNSAFE and MUST be sanitised before display.
- **PHP 8.4+:** Required, because `tempest/highlight` needs it from 2.26 onward.

### Usage

@verbatim
<code-snippet name="markdown-render" lang="php">
use JeffersonGoncalves\Markdown\Markdown;

$html = Markdown::render('# Hello **world**');

// With heading permalink anchors (class "md-anchor")
$html = Markdown::render($readme, headingPermalinks: true);

// Per-call option overrides (this call only)
$html = Markdown::render($trusted, options: ['html_input' => 'allow']);

// Via the facade
use JeffersonGoncalves\Markdown\Facades\Markdown as MarkdownFacade;
$html = MarkdownFacade::render('# Hi');
</code-snippet>
@endverbatim

In Blade:

@verbatim
<code-snippet name="markdown-blade" lang="blade">
@markdown('# Hello **world**')
@markdown($post->body, true)
</code-snippet>
@endverbatim

### Configuration

Published to `config/markdown.php`:

| Key | Default | Purpose |
|-----|---------|---------|
| `html_input` | `'escape'` | Raw-HTML handling: `allow`, `escape`, or `strip`. |
| `allow_unsafe_links` | `false` | Strip unsafe link protocols (javascript:, data:, …). |
| `heading_permalink.symbol` | `'#'` | Symbol rendered inside each permalink anchor. |
| `heading_permalink.html_class` | `'md-anchor'` | Class added to each permalink anchor. |

Any of these can be overridden per call via the `$options` array.

### Security
- `html_input` defaults to `escape`, so raw HTML in untrusted sources is rendered as visible text rather than live markup — safe out of the box.
- When you set `html_input => allow` (config or per-call), the output is UNSAFE: pass it through an HTML sanitizer (e.g. `jeffersongoncalves/laravel-html-sanitizer`) before displaying untrusted content (third-party READMEs, imported article bodies).
- Class-based highlight tokens (`.hl-*`) survive sanitisation; inline-style highlighting would not — that is why `CssTheme` is used.

### Conventions
- The renderer is a stateless static helper — call `Markdown::render()` directly, or use the `Markdown` facade / `@markdown` directive.
- Style the `.hl-*` token classes (and `.md-anchor` when using permalinks) in your own CSS.
