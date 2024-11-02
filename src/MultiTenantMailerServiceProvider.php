<?php

namespace Khaleejinfotech\MultiTenantMailer;

use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;
use Khaleejinfotech\MultiTenantMailer\Channels\MultiTenantMailerChannel;
use Khaleejinfotech\MultiTenantMailer\Contracts\MultiTenantMailerSettings;

class MultiTenantMailerServiceProvider extends ServiceProvider
{

    public function boot(): void
    {
        AboutCommand::add('Multi Tenant Mailer', fn() => [
            'Version' => '1.0.8',
            'Developed by' => 'KHALEEJ Infotech',
            'Developer Email' => 'contact@khaleejinfotech.com',
            'Developer Website' => 'https://khaleejinfotech.com',
            'Author' => 'Bilal Khilji',
        ]);

        $this->mergeConfigFrom(__DIR__ . '/config/multi-tenant-mailer.php', 'multi-tenant-mailer');

        $this->publishes([
            __DIR__ . '/config/multi-tenant-mailer.php' => config_path('multi-tenant-mailer.php')
        ], 'multi-tenant-mailer');
    }

    public function register(): void
    {
        parent::register();
        $this->app->singleton(MultiTenantMailer::class, function () {
            return new MultiTenantMailer;
        });

        $this->app->singleton(MultiTenantMailerSettings::class, function () {
            return new MultiTenantMailerSettings;
        });

        Notification::resolved(function (ChannelManager $service) {
            $service->extend('tenant_mailer', fn($app) => $app->make(MultiTenantMailerChannel::class));
        });
    }
}
