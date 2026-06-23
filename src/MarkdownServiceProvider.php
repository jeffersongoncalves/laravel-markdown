<?php

namespace JeffersonGoncalves\Markdown;

use Illuminate\Support\Facades\Blade;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class MarkdownServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-markdown')
            ->hasConfigFile();
    }

    public function packageBooted(): void
    {
        // @markdown($source) — and @markdown($source, true) for permalink
        // anchors — echoes the rendered HTML. Output is HTML, so it is echoed
        // raw; sanitise the source/output yourself when it is untrusted.
        Blade::directive('markdown', function (string $expression): string {
            return "<?php echo \JeffersonGoncalves\Markdown\Markdown::render({$expression}); ?>";
        });
    }
}
