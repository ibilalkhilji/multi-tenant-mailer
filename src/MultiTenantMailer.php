<?php

namespace Khaleejinfotech\MultiTenantMailer;

use BackedEnum;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Markdown;
use Illuminate\Notifications\AnonymousNotifiable;
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
    protected ?Swift_Mailer $mailer = null;
    protected ?Swift_SmtpTransport $transport = null;

    protected bool $fallbackConfig = false;
    protected string $host;
    protected int $port;
    protected string $username;
    protected string $password;
    protected string $encryption;
    protected array|string $fromAddresses;
    protected ?string $fromName = null;
    protected array|string $toAddresses;
    protected array $ccAddresses = [];
    protected array $bccAddresses = [];
    protected ?string $toName = null;
    protected string $body;
    protected string $bodyPart = '';
    protected array $attachments = [];
    protected array $headers = [];
    protected string $contentType = 'text/html';
    protected ?string $subject = null;
    protected bool $shouldQueue = false;
    protected null|string|BackedEnum $onQueue = null;
    protected ?array $streamOptions = null;
    protected bool $shouldStopTransport = true;

    public function __construct()
    {
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
     * Sets default email settings if there is no email config set.
     *
     * @param bool $fallbackConfig
     * @return MultiTenantMailer
     */
    public function useFallbackConfig(bool $fallbackConfig): MultiTenantMailer
    {
        $this->fallbackConfig = $fallbackConfig;
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
        if (isset($this->host)) return $this->host;

        if ($this->fallbackConfig) return config('mail.mailers.smtp.host');

        throw new MultiTenantMailerException('Host server is not set');
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
        if (isset($this->port)) return $this->port;

        if ($this->fallbackConfig) return config('mail.mailers.smtp.port');

        throw new MultiTenantMailerException('Host port is not set');
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
        if (isset($this->username)) return $this->username;

        if ($this->fallbackConfig) return config('mail.mailers.smtp.username');

        throw new MultiTenantMailerException('Host username is not set');
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
        if (isset($this->password)) return $this->password;

        if ($this->fallbackConfig) return config('mail.mailers.smtp.password');

        throw new MultiTenantMailerException('Host password is not set');
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
     * @throws MultiTenantMailerException
     */
    public function getEncryption(): string
    {
        if (isset($this->encryption)) return $this->encryption;

        if ($this->fallbackConfig) return config('mail.mailers.smtp.encryption');

        throw new MultiTenantMailerException('Encryption is not set');
    }

    /**
     * Sets the recipient email address and name.
     *
     * @param array|string $addresses Email address(es) of the recipient.
     * @param string|null $name Name of the recipient (optional).
     * @return MultiTenantMailer
     */
    public function setTo(array|string|AnonymousNotifiable $addresses, string $name = null): MultiTenantMailer
    {
        $this->toAddresses = $addresses->routes['tenant_mailer'] ?? $addresses;
        $this->toName = $name;
        return $this;
    }

    /**
     * Retrieves the recipient email address.
     *
     * @return array|string The recipient email address(es).
     * @throws MultiTenantMailerException
     */
    public function getToAddresses(): array|string
    {
        if (isset($this->toAddresses)) return $this->toAddresses;

        throw new MultiTenantMailerException('To Addresses is not set');
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
     * Sets the cc recipient email addresses.
     *
     * @param array $addresses Email address(es) of the cc recipient.
     * @return MultiTenantMailer
     */
    public function setCc(array $addresses): MultiTenantMailer
    {
        $this->ccAddresses = $addresses;
        return $this;
    }

    /**
     * Retrieves the cc recipient addresses.
     *
     * @return array The cc recipient addresses.
     */
    public function getCc(): array
    {
        return $this->ccAddresses;
    }

    /**
     * Sets the bcc recipient email addresses.
     *
     * @param array $addresses Email address(es) of the bcc recipient.
     * @return MultiTenantMailer
     */
    public function setBcc(array $addresses): MultiTenantMailer
    {
        $this->ccAddresses = $addresses;
        return $this;
    }

    /**
     * Retrieves the bcc recipient addresses.
     *
     * @return array The cc recipient addresses.
     */
    public function getBcc(): array
    {
        return $this->bccAddresses;
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
        if (isset($this->fromAddresses)) return $this->fromAddresses;

        if ($this->fallbackConfig) return config('mail.from.address');

        throw new MultiTenantMailerException('From address must be set');
    }

    /**
     * Retrieves the sender name.
     *
     * @return string|null The sender name.
     */
    public function getFromName(): ?string
    {
        if (isset($this->fromName)) return $this->fromName;

        return config('mail.mailers.smtp.host');
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
        if ($this->subject != null) return $this->subject;

        throw new MultiTenantMailerException('Subject not set');
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
     * Retrieves the job queue name.
     *
     * @return BackedEnum|string|null Returns the queue name or identifier if set,
     *                                or null if no specific queue is assigned.
     */
    public function getQueue(): BackedEnum|string|null
    {
        return $this->onQueue;
    }

    /**
     * Sets the job queue name.
     *
     * @param null|BackedEnum|string $onQueue The queue name or identifier to be set,
     *                                        or null to remove any specific queue assignment.
     * @return MultiTenantMailer Returns the instance of MultiTenantMailer for method chaining.
     */
    public function onQueue(null|BackedEnum|string $onQueue = 'default'): MultiTenantMailer
    {
        $this->onQueue = $onQueue;
        return $this;
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
            if (method_exists($body, 'getSubject') && $body->getSubject()) {
                $this->subject = $body->getSubject();
            } else if (method_exists($body, 'envelope') && $body->envelope()?->subject) {
                $this->subject = $body->envelope()->subject;
            } elseif ($body->subject) {
                $this->subject = $body->subject;
            }

            if (method_exists($body, 'headers')) {
                if ($body->headers()->messageId) {
                    $this->headers = array_merge($this->headers, ['Message-Id' => $body->headers()->messageId]);
                }

                if ($body->headers()->references) {
                    $references = [];
                    foreach ($body->headers()->references as $key => $value)
                        $references[$key] = $value;
                    $this->headers = array_merge($this->headers, $references);
                }

                if ($body->headers()->text) {
                    $texts = [];
                    foreach ($body->headers()->text as $key => $value)
                        $texts[$key] = $value;
                    $this->headers = array_merge($this->headers, $texts);
                }
            }

            $this->body = $body->render();
        } elseif ($body instanceof Notification) {
            $message = $body->toMail($this->getToAddresses());
            $this->subject = $message->subject;
            if ($message->attachments) $this->attachments = $message->attachments;
            $this->body = $message->markdown != null
                ? app()->make(Markdown::class)->render($message->markdown, $message->data())
                : $message->render();
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
     * @return MultiTenantMailer Returns the current instance for method chaining.
     */
    public function setBodyPart(string $bodyPart): MultiTenantMailer
    {
        $this->bodyPart = $bodyPart;
        return $this;
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
     * Get the headers for the mailer.
     *
     * @return array An array of headers currently set for the mailer.
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Set the headers for the mailer.
     *
     * @param array $headers An array of headers to be set for the mailer.
     *
     * @return MultiTenantMailer
     */
    public function setHeaders(array $headers): MultiTenantMailer
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * Define whether to keep transport running or stopped.
     *
     * @param bool $b
     * @return MultiTenantMailer
     */
    public function stopTransport(bool $b = true): MultiTenantMailer
    {
        $this->shouldStopTransport = $b;
        return $this;
    }

    /**
     * To stop the transport
     *
     * @return bool
     */
    public function shouldStopTransport(): bool
    {
        return $this->shouldStopTransport;
    }

    /**
     * Sends the email or queues it if required.
     *
     * @return int The number of recipients who were accepted for delivery.
     * @throws Exception if there is an issue with sending the email.
     */
    public function send(): int
    {
        $response = 0;
        try {
            // Create a message
            $message = $this->getMessage();

            // Access the headers
            $headers = $message->getHeaders();

            // Update the headers
            foreach ($this->getHeaders() as $key => $header) {
                if ($headers->has($key)) $headers->remove($key);
                $headers->addTextHeader($key, $header);
            }

            foreach ($this->getAttachments() as $attachment) {
                $message->attach(Swift_Attachment::fromPath($attachment['file']));
            }

            if ($this->isShouldQueue()) {
                $queueClass = $this->getQueueJobClass();
                dispatch((new $queueClass($this->getMailer(), $message))->onQueue($this->getQueue()));
                return 1;
            }

            $response = $this->getMailer()->send($message);

            if ($response > 0)
                MailSuccess::dispatch($message->getId());
            else
                MailFailed::dispatch();
        } catch (Exception$exception) {
            MailFailed::dispatch();
            logger()->error('Mail sending failed: ' . $exception->getMessage());
            $response = 0;
        } finally {
            if ($this->shouldStopTransport() && $this->transport != null)
                $this->getTransport()->stop();
        }

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
        if ($this->transport == null) {
            $this->transport = new Swift_SmtpTransport($this->getHost(), $this->getPort());
            $this->transport->setUsername($this->getUsername());
            $this->transport->setPassword($this->getPassword());
            $this->transport->setEncryption($this->getEncryption());
            if ($this->getStreamOptions() != null)
                $this->transport->setStreamOptions($this->getStreamOptions());
        }
        return $this->transport;
    }

    /**
     * Creates and retrieves the SwiftMailer instance.
     *
     * @return Swift_Mailer The SwiftMailer instance.
     * @throws MultiTenantMailerException
     */
    private function getMailer(): Swift_Mailer
    {
        if ($this->mailer == null)
            $this->mailer = new Swift_Mailer($this->getTransport());
        return $this->mailer;
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
            ->setCc($this->getCc())
            ->setBcc($this->getBcc())
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
