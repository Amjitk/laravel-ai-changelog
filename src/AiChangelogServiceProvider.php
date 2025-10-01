<?php

namespace Amjitk\AiChangelog;

use Illuminate\Support\ServiceProvider;
use Amjitk\AiChangelog\Console\AiChangelogCommand;

class AiChangelogServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/ai-changelog.php' => config_path('ai-changelog.php'),
        ], 'ai-changelog-config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                AiChangelogCommand::class,
            ]);
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/ai-changelog.php', 'ai-changelog'
        );

        $this->app->singleton(ChangelogGenerator::class, function ($app) {
            return new ChangelogGenerator(config('ai-changelog'));
        });
    }
}