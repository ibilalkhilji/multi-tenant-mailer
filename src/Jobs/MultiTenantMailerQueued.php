<?php

namespace Khaleejinfotech\MultiTenantMailer\Jobs;

use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Khaleejinfotech\MultiTenantMailer\Events\MailFailed;
use Khaleejinfotech\MultiTenantMailer\Events\MailSuccess;
use Swift_Mailer;
use Swift_Message;

class MultiTenantMailerQueued implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Swift_Mailer $mailer, public Swift_Message $message)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $this->mailer->send($this->message);
            MailSuccess::dispatch();
        } catch (Exception $e) {
            MailFailed::dispatch($e->getMessage());
        }
    }
}
