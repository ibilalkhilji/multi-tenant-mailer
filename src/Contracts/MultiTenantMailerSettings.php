<?php

namespace Khaleejinfotech\MultiTenantMailer\Contracts;

/**
 * Class MultiTenantMailerSettings
 *
 * This class holds the configuration settings for the multi-tenant mailer.
 * It includes the SMTP settings such as host, port, username, password, encryption,
 * as well as the sender's email address and optional name.
 *
 * @package Khaleejinfotech/MultiTenantMailer
 */
class MultiTenantMailerSettings
{
    /**
     * @var string The SMTP host address.
     */
    protected string $host;

    /**
     * @var int The SMTP port number.
     */
    protected int $port;

    /**
     * @var string The SMTP username for authentication.
     */
    protected string $username;

    /**
     * @var string The SMTP password for authentication.
     */
    protected string $password;

    /**
     * @var string The encryption type used (tls/ssl).
     */
    protected string $encryption;

    /**
     * @var string The sender's email address.
     */
    protected string $fromAddress;

    /**
     * @var string|null The optional sender's name.
     */
    protected ?string $fromName = null;

    /**
     * Sets the SMTP host.
     *
     * @param string $host The SMTP host address.
     * @return MultiTenantMailerSettings
     */
    public function setHost(string $host): MultiTenantMailerSettings
    {
        $this->host = $host;
        return $this;
    }

    /**
     * Retrieves the SMTP host.
     *
     * @return string The SMTP host address.
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * Sets the SMTP port.
     *
     * @param int $port The SMTP port number.
     * @return MultiTenantMailerSettings
     */
    public function setPort(int $port): MultiTenantMailerSettings
    {
        $this->port = $port;
        return $this;
    }

    /**
     * Retrieves the SMTP port.
     *
     * @return int The SMTP port number.
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * Sets the SMTP username.
     *
     * @param string $username The SMTP username for authentication.
     * @return MultiTenantMailerSettings
     */
    public function setUsername(string $username): MultiTenantMailerSettings
    {
        $this->username = $username;
        return $this;
    }

    /**
     * Retrieves the SMTP username.
     *
     * @return string The SMTP username for authentication.
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * Sets the SMTP password.
     *
     * @param string $password The SMTP password for authentication.
     * @return MultiTenantMailerSettings
     */
    public function setPassword(string $password): MultiTenantMailerSettings
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Retrieves the SMTP password.
     *
     * @return string The SMTP password for authentication.
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Sets the encryption type.
     *
     * @param string $encryption The encryption type (tls/ssl).
     * @return MultiTenantMailerSettings
     */
    public function setEncryption(string $encryption): MultiTenantMailerSettings
    {
        $this->encryption = $encryption;
        return $this;
    }

    /**
     * Retrieves the encryption type.
     *
     * @return string The encryption type (tls/ssl).
     */
    public function getEncryption(): string
    {
        return $this->encryption;
    }

    /**
     * Sets the sender's name.
     *
     * @param string|null $name The sender's name (optional).
     * @return MultiTenantMailerSettings
     */
    public function setFromName(?string $name = null): MultiTenantMailerSettings
    {
        $this->fromName = $name;
        return $this;
    }

    /**
     * Retrieves the sender's name.
     *
     * @return string|null The sender's name (optional).
     */
    public function getFromName(): ?string
    {
        return $this->fromName;
    }

    /**
     * Sets the sender's email address and optional name.
     *
     * @param string $addresses The sender's email address.
     * @param string|null $name The sender's name (optional).
     * @return MultiTenantMailerSettings
     */
    public function setFromAddress(string $addresses, ?string $name = null): MultiTenantMailerSettings
    {
        $this->fromAddress = $addresses;
        $this->fromName = $name;
        return $this;
    }

    /**
     * Retrieves the sender's email address.
     *
     * @return string|null The sender's email address.
     */
    public function getFromAddress(): ?string
    {
        return $this->fromAddress;
    }
}

