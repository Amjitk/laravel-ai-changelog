<?php

namespace Amjithk\AiChangelog;

use Illuminate\Support\ServiceProvider;
use Amjithk\AiChangelog\Console\AiChangelogCommand;

class AiChangelogServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Publish the configuration file
        $this->publishes([
            __DIR__.'/config/ai-changelog.php' => $this->app->configPath('ai-changelog.php'),
        ], 'ai-changelog-config');

        // Register the artisan command
        if ($this->app->runningInConsole()) {
            $this->commands([
                AiChangelogCommand::class,
            ]);
        }
    }

    public function register()
    {
        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__.'/config/ai-changelog.php', 'ai-changelog'
        );

        // Bind the core logic to the service container
        $this->app->singleton(ChangelogGenerator::class, function ($app) {
            return new ChangelogGenerator($app->config('ai-changelog'));
        });
    }
}