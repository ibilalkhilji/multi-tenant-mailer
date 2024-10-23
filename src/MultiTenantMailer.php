<?php

namespace Khaleejinfotech\MultiTenantMailer;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Markdown;
use Illuminate\Notifications\Notification;
use Khaleejinfotech\MultiTenantMailer\Contracts\MultiTenantMailerSettings;
use Khaleejinfotech\MultiTenantMailer\Events\MailFailed;
use Khaleejinfotech\MultiTenantMailer\Events\MailSuccess;
use Khaleejinfotech\MultiTenantMailer\Exceptions\MultiTenantMailerException;
use ReflectionException;
use Swift_Attachment;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;

/**
 * Class MultiTenantMailer
 *
 * This class handles sending emails in a multi-tenant environment.
 * It supports configuring the SMTP host, port, encryption, credentials, and sending email notifications with attachments.
 * It can queue emails if required and renders email body from notification instances.
 *
 * @package Khaleejinfotech/MultiTenantMailer
 */
class MultiTenantMailer
{
    protected string $host;
    protected int $port;
    protected string $username;
    protected string $password;
    protected string $encryption;
    protected array|string $fromAddresses;
    protected ?string $fromName = null;

    protected array|string $toAddresses;
    protected ?string $toName = null;

    protected string $body;
    protected string $bodyPart;
    protected array $attachments = [];

    protected string $contentType = 'text/html';
    protected ?string $notificationSubject = null;
    protected ?string $subject = null;
    protected bool $shouldQueue = false;
    protected ?array $streamOptions = null;

    public function __construct()
    {
        //
    }

    /**
     * Initializes the mailer with SMTP settings.
     *
     * @param string $host SMTP host address.
     * @param int $port SMTP port number.
     * @param string $username Username for SMTP authentication.
     * @param string $password Password for SMTP authentication.
     * @param string $encryption Encryption type (tls/ssl).
     * @return MultiTenantMailer
     */
    public function init(string $host, int $port, string $username, string $password, string $encryption): MultiTenantMailer
    {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->encryption = $encryption;
        return $this;
    }

    /**
     * Sets email settings from a MultiTenantMailerSettings object.
     *
     * @param MultiTenantMailerSettings $mailerSettings Settings object containing host, port, and other SMTP details.
     * @return MultiTenantMailer
     */
    public function withSettings(MultiTenantMailerSettings $mailerSettings): MultiTenantMailer
    {
        $this->host = $mailerSettings->getHost();
        $this->port = $mailerSettings->getPort();
        $this->username = $mailerSettings->getUsername();
        $this->password = $mailerSettings->getPassword();
        $this->encryption = $mailerSettings->getEncryption();
        $this->fromAddresses = $mailerSettings->getFromAddress();
        $this->fromName = $mailerSettings->getFromName();
        return $this;
    }

    /**
     * Sets the SMTP host.
     *
     * @param string $host The SMTP host address.
     * @return MultiTenantMailer
     */
    public function setHost(string $host): MultiTenantMailer
    {
        $this->host = $host;
        return $this;
    }

    /**
     * Retrieves the SMTP host.
     *
     * @return string The SMTP host.
     * @throws MultiTenantMailerException if the host is not set.
     */
    public function getHost(): string
    {
        if (!isset($this->host))
            throw new MultiTenantMailerException('Host server is not set');
        return $this->host;
    }

    /**
     * Sets the SMTP port.
     *
     * @param int $port The SMTP port number.
     * @return MultiTenantMailer
     */
    public function setPort(int $port): MultiTenantMailer
    {
        $this->port = $port;
        return $this;
    }

    /**
     * Retrieves the SMTP port.
     *
     * @return int The SMTP port.
     * @throws MultiTenantMailerException if the port is not set.
     */
    public function getPort(): int
    {
        if (!isset($this->port))
            throw new MultiTenantMailerException('Host port is not set');
        return $this->port;
    }

    /**
     * Sets the SMTP username.
     *
     * @param string $username The username for SMTP authentication.
     * @return MultiTenantMailer
     */
    public function setUsername(string $username): MultiTenantMailer
    {
        $this->username = $username;
        return $this;
    }

    /**
     * Retrieves the SMTP username.
     *
     * @return string The SMTP username.
     * @throws MultiTenantMailerException if the username is not set.
     */
    public function getUsername(): string
    {
        if (!isset($this->username))
            throw new MultiTenantMailerException('Host username is not set');
        return $this->username;
    }

    /**
     * Sets the SMTP password.
     *
     * @param string $password The password for SMTP authentication.
     * @return MultiTenantMailer
     */
    public function setPassword(string $password): MultiTenantMailer
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Retrieves the SMTP password.
     *
     * @return string The SMTP password.
     * @throws MultiTenantMailerException if the password is not set.
     */
    public function getPassword(): string
    {
        if (!isset($this->password))
            throw new MultiTenantMailerException('Host password is not set');
        return $this->password;
    }

    /**
     * Sets the encryption type (tls/ssl).
     *
     * @param string $encryption Encryption type.
     * @return MultiTenantMailer
     */
    public function setEncryption(string $encryption): MultiTenantMailer
    {
        $this->encryption = $encryption;
        return $this;
    }

    /**
     * Retrieves the encryption type.
     *
     * @return string The encryption type.
     */
    public function getEncryption(): string
    {
        return $this->encryption;
    }

    /**
     * Sets the recipient email address and name.
     *
     * @param array|string $addresses Email address(es) of the recipient.
     * @param string|null $name Name of the recipient (optional).
     * @return MultiTenantMailer
     */
    public function setTo(array|string $addresses, string $name = null): MultiTenantMailer
    {
        $this->toAddresses = $addresses;
        $this->toName = $name;
        return $this;
    }

    /**
     * Retrieves the recipient email address.
     *
     * @return array|string The recipient email address(es).
     */
    public function getToAddresses(): array|string
    {
        return $this->toAddresses;
    }

    /**
     * Retrieves the recipient name.
     *
     * @return string|null The recipient name.
     */
    public function getToName(): ?string
    {
        return $this->toName;
    }

    /**
     * Sets the sender email address and name.
     *
     * @param array|string $addresses Sender email address(es).
     * @param string|null $name Sender name (optional).
     * @return MultiTenantMailer
     */
    public function setFrom(array|string $addresses, string $name = null): MultiTenantMailer
    {
        $this->fromAddresses = $addresses;
        $this->fromName = $name;
        return $this;
    }

    /**
     * Retrieves the sender email address.
     *
     * @return array|string The sender email address(es).
     * @throws MultiTenantMailerException if the sender email address is not set.
     */
    public function getFromAddresses(): array|string
    {
        if (!isset($this->fromAddresses))
            throw new MultiTenantMailerException('From address must be set');
        return $this->fromAddresses;
    }

    /**
     * Retrieves the sender name.
     *
     * @return string|null The sender name.
     */
    public function getFromName(): ?string
    {
        return $this->fromName;
    }

    /**
     * Sets the email subject.
     *
     * @param string $subject The email subject.
     * @return MultiTenantMailer
     */
    public function setSubject(string $subject): MultiTenantMailer
    {
        $this->subject = $subject;
        $this->notificationSubject = $subject;
        return $this;
    }

    /**
     * Retrieves the email subject.
     *
     * @return string The email subject.
     * @throws MultiTenantMailerException if the subject is not set.
     */
    private function getSubject(): string
    {
        if ($this->subject == null && $this->notificationSubject == null) {
            throw new MultiTenantMailerException('Subject not set');
        }

        return $this->subject ?? $this->notificationSubject;
    }

    /**
     * Sets the content type of the email.
     *
     * @param string $contentType The content type (default is text/html).
     * @return MultiTenantMailer
     */
    public function setContentType(string $contentType = 'text/html'): MultiTenantMailer
    {
        $this->contentType = $contentType;
        return $this;
    }

    /**
     * Retrieves the content type of the email.
     *
     * @return string The content type.
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * Marks the email to be queued.
     *
     * @return MultiTenantMailer
     */
    public function shouldQueue(): MultiTenantMailer
    {
        $this->shouldQueue = true;
        return $this;
    }

    /**
     * Checks if the email should be queued.
     *
     * @return bool True if the email should be queued, false otherwise.
     */
    public function isShouldQueue(): bool
    {
        return $this->shouldQueue;
    }

    /**
     * Get the stream options.
     *
     * @return array|null The stream options, or null if not set.
     */
    public function getStreamOptions(): ?array
    {
        return $this->streamOptions;
    }

    /**
     * Set the stream options.
     *
     * @param array|null $streamOptions The stream options to set.
     *
     * @return MultiTenantMailer
     */
    public function setStreamOptions(?array $streamOptions): MultiTenantMailer
    {
        $this->streamOptions = $streamOptions;
        return $this;
    }

    /**
     * Sets the body of the email using a Notification, Mailable, or a string.
     *
     * This method processes the provided body content and extracts necessary
     * information like subject, attachments, and rendered content from the
     * Notification or Mailable instance. If a plain string is provided, it
     * sets that as the email body.
     *
     * @param Mailable|Notification|string $body The notification or mailable instance, or the email body content.
     * @return MultiTenantMailer Returns the current instance for method chaining.
     * @throws BindingResolutionException If there is an issue resolving the notification or mailable instance.
     * @throws ReflectionException
     */
    public function setBody(Mailable|Notification|string $body): MultiTenantMailer
    {
        if ($body instanceof Mailable) {
            if($body->subject){
                $this->notificationSubject = $body->subject;
                $this->subject = $body->subject;
            }
            $this->body = $body->render();
            $this->bodyPart = strip_tags($this->body);
            $this->attachments = [];
        } elseif ($body instanceof Notification) {
            $message = $body->toMail($this->getToAddresses());
            $this->notificationSubject = $message->subject;
            $this->attachments = $message->attachments ?? [];
            $this->body = $message->markdown != null
                ? app()->make(Markdown::class)->render($message->markdown, $message->data())
                : $message->render();
            $this->bodyPart = strip_tags($message->render());
        } else {
            $this->body = $body;
        }

        return $this;
    }

    /**
     * Retrieves the body of the email.
     *
     * @return string The body content of the email.
     * @throws MultiTenantMailerException if the body is not set.
     */
    public function getBody(): string
    {
        if (!isset($this->body))
            throw new MultiTenantMailerException('Body must not be empty');
        return $this->body;
    }

    /**
     * Sets the plain-text part of the email body.
     *
     * @param string $bodyPart The plain-text part of the email.
     */
    public function setBodyPart(string $bodyPart): void
    {
        $this->bodyPart = $bodyPart;
    }

    /**
     * Retrieves the plain-text part of the email body.
     *
     * @return string The plain-text part of the email.
     */
    public function getBodyPart(): string
    {
        return $this->bodyPart;
    }

    /**
     * Sets the email attachments.
     *
     * @param array $attachments Array of file paths for the attachments.
     */
    public function setAttachments(array $attachments): void
    {
        $this->attachments = $attachments;
    }

    /**
     * Retrieves the email attachments.
     *
     * @return array Array of file paths for the attachments.
     */
    public function getAttachments(): array
    {
        return $this->attachments;
    }

    /**
     * Sends the email or queues it if required.
     *
     * @return int The number of recipients who were accepted for delivery.
     * @throws Exception if there is an issue with sending the email.
     */
    public function send(): int
    {
        // Create a message
        $message = $this->getMessage();

        foreach ($this->getAttachments() as $attachment) {
            $message->attach(Swift_Attachment::fromPath($attachment['file']));
        }

        if ($this->shouldQueue) {
            $queueClass = $this->getQueueJobClass();
            dispatch(new $queueClass($this->getMailer(), $message));
            return 1;
        }
        $response = $this->getMailer()->send($message);

        if ($response) MailSuccess::dispatch();
        else            MailFailed::dispatch();

        return $response;
    }

    /**
     * Creates and retrieves the SMTP transport instance.
     *
     * @return Swift_SmtpTransport The SMTP transport instance.
     * @throws MultiTenantMailerException
     */
    private function getTransport(): Swift_SmtpTransport
    {
        $transport = new Swift_SmtpTransport($this->getHost(), $this->getPort());
        $transport->setUsername($this->getUsername());
        $transport->setPassword($this->getPassword());
        $transport->setEncryption($this->getEncryption());
        if ($this->getStreamOptions() != null)
            $transport->setStreamOptions($this->getStreamOptions());
        return $transport;
    }

    /**
     * Creates and retrieves the SwiftMailer instance.
     *
     * @return Swift_Mailer The SwiftMailer instance.
     * @throws MultiTenantMailerException
     */
    private function getMailer(): Swift_Mailer
    {
        return new Swift_Mailer($this->getTransport());
    }

    /**
     * Creates and configures the SwiftMessage instance.
     *
     * @return Swift_Message The SwiftMessage instance.
     * @throws MultiTenantMailerException
     */
    private function getMessage(): Swift_Message
    {
        return (new Swift_Message($this->getSubject()))
            ->setFrom($this->getFromAddresses(), $this->getFromName())
            ->setTo($this->getToAddresses(), $this->getToName())
            ->setContentType($this->getContentType())
            ->setBody($this->getBody())
            ->addPart($this->getBodyPart(), 'text/plain');
    }

    /**
     * @throws MultiTenantMailerException
     */
    public function getQueueJobClass(): string
    {
        $queueJobClass = config('multi-tenant-mailer.queue_class');

        if ($queueJobClass == null || $queueJobClass == '')
            throw new MultiTenantMailerException("Queue class not defined");

        return $queueJobClass;
    }
}
