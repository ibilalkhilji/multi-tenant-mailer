<?php

namespace Khaleejinfotech\MultiTenantMailer\Facades;

use Illuminate\Support\Facades\Facade;
use Khaleejinfotech\MultiTenantMailer\Contracts\MultiTenantMailerSettings;
use Khaleejinfotech\MultiTenantMailer\MultiTenantMailer as MultiTenantMailerService;

/**
 * Class MultiTenantMailerFacade
 *
 * @method static MultiTenantMailerService init(string $host, int $port, string $username, string $password, string $encryption)
 * @method static MultiTenantMailerService withSettings(MultiTenantMailerSettings $mailerSettings)
 * @method static MultiTenantMailerService setHost(string $host)
 * @method static string getHost()
 * @method static MultiTenantMailerService setPort(int $port)
 * @method static int getPort()
 * @method static MultiTenantMailerService setUsername(string $username)
 * @method static string getUsername()
 * @method static MultiTenantMailerService setPassword(string $password)
 * @method static string getPassword()
 * @method static MultiTenantMailerService setEncryption(string $encryption)
 * @method static string getEncryption()
 * @method static MultiTenantMailerService setTo(array|string $addresses, string $name = null)
 * @method static array|string getToAddresses()
 * @method static null|string getToName()
 * @method static MultiTenantMailerService setFrom(array|string $addresses, string $name = null)
 * @method static array|string getFromAddresses()
 * @method static null|string getFromName()
 * @method static MultiTenantMailerService setSubject(string $subject)
 * @method static MultiTenantMailerService setContentType(string $contentType = 'text/html')
 * @method static string getContentType()
 * @method static MultiTenantMailerService shouldQueue()
 * @method static bool isShouldQueue()
 * @method static MultiTenantMailerService setBody($notification)
 * @method static string getBody()
 * @method static MultiTenantMailerService setBodyPart(string $bodyPart)
 * @method static string getBodyPart()
 * @method static MultiTenantMailerService setAttachments(array $attachments)
 * @method static array getAttachments()
 * @method static int send()
 *
 * @package Khaleejinfotech/MultiTenantMailer
 */
class MultiTenantMailer extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return MultiTenantMailerService::class;
    }
}

