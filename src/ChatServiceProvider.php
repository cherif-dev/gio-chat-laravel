<?php

namespace GenitIo\Chat;

use Illuminate\Support\ServiceProvider;

class ChatServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Publish migrations
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'chat-migrations');

            // Publish config
            $this->publishes([
                __DIR__ . '/../config/chat.php' => config_path('chat.php'),
            ], 'chat-config');
        }

        // Load config
        $this->mergeConfigFrom(
            __DIR__ . '/../config/chat.php',
            'chat'
        );
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        // Register the ChatService as a singleton
        $this->app->singleton('chat', function ($app) {
            return new \GenitIo\Chat\Services\ChatService();
        });
    }
}
