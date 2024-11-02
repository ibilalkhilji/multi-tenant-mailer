<?php

namespace Khaleejinfotech\MultiTenantMailer\Channels;

use Exception;
use Illuminate\Events\Dispatcher;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\Notification;

class MultiTenantMailerChannel
{
    public function __construct(private readonly Dispatcher $dispatcher)
    {
    }

    /**
     * @throws Exception
     */
    public function send(mixed $notifiable, Notification $notification): ?int
    {
        $message = $notification->toTenantMailer($notifiable);

        try {
            $response = $message->send();
        } catch (Exception $exception) {
            $this->dispatcher->dispatch(new NotificationFailed($notifiable, $notification, 'tenant_mailer'));
            throw $exception;
        }

        return $response;
    }
}
