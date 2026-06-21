<?php

namespace JeffersonGoncalves\Markdown\Tests;

use JeffersonGoncalves\Markdown\Markdown;
use JeffersonGoncalves\Markdown\MarkdownServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        // The converter is memoized statically; reset it between tests so each
        // test sees its own markdown configuration.
        Markdown::flush();
    }

    protected function getPackageProviders($app)
    {
        return [
            MarkdownServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
    }
}
