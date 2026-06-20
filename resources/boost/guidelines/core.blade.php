## Laravel Markdown

### Overview
Laravel Markdown is a shared CommonMark renderer. It converts Markdown to HTML with GitHub Flavored Markdown, optional heading permalinks, and server-side syntax highlighting on fenced code blocks via tempest/highlight's class-based `CssTheme`.

**Namespace:** `JeffersonGoncalves\Markdown`
**Service Provider:** `MarkdownServiceProvider` (auto-discovered)
**Main class:** `JeffersonGoncalves\Markdown\Markdown`

### Key Concepts
- **Static API:** `Markdown::render(string $markdown, bool $headingPermalinks = false): string`.
- **GitHub Flavored Markdown:** Tables, task lists, strikethrough, autolinks are enabled.
- **Syntax highlighting:** Only the block-level `FencedCode` renderer is overridden (priority 10) — it emits `<span class="hl-…">` tokens. Inline `code` keeps plain rendering.
- **Unsafe output:** Renders with `html_input => allow`. The output is UNSAFE for untrusted input and MUST be sanitised before display.

### Usage

@verbatim
<code-snippet name="markdown-render" lang="php">
use JeffersonGoncalves\Markdown\Markdown;

$html = Markdown::render('# Hello **world**');

// With heading permalink anchors (class "md-anchor")
$html = Markdown::render($readme, headingPermalinks: true);
</code-snippet>
@endverbatim

### Configuration

Published to `config/markdown.php`:

| Key | Default | Purpose |
|-----|---------|---------|
| `html_input` | `'allow'` | Raw-HTML handling: `allow`, `escape`, or `strip`. |
| `allow_unsafe_links` | `false` | Strip unsafe link protocols (javascript:, data:, …). |
| `heading_permalink.symbol` | `'#'` | Symbol rendered inside each permalink anchor. |
| `heading_permalink.html_class` | `'md-anchor'` | Class added to each permalink anchor. |

### Security
- Because `html_input` defaults to `allow`, raw HTML in the source is preserved in the output.
- Always pass the output through an HTML sanitizer (e.g. `jeffersongoncalves/laravel-html-sanitizer`) before displaying untrusted content (third-party READMEs, imported article bodies).
- Class-based highlight tokens (`.hl-*`) survive sanitisation; inline-style highlighting would not — that is why `CssTheme` is used.

### Conventions
- The renderer is a stateless static helper — call `Markdown::render()` directly.
- Style the `.hl-*` token classes (and `.md-anchor` when using permalinks) in your own CSS.
