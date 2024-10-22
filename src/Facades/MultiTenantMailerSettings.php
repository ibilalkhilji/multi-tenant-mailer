<?php

namespace Khaleejinfotech\MultiTenantMailer\Facades;

use Illuminate\Support\Facades\Facade;
use Khaleejinfotech\MultiTenantMailer\Contracts\MultiTenantMailerSettings as MultiTenantMailerSettingsContract;

/**
 * Class MultiTenantMailerSettingsFacade
 *
 * @method static MultiTenantMailerSettingsContract setHost(string $host)
 * @method static string getHost()
 * @method static MultiTenantMailerSettingsContract setPort(int $port)
 * @method static int getPort()
 * @method static MultiTenantMailerSettingsContract setUsername(string $username)
 * @method static string getUsername()
 * @method static MultiTenantMailerSettingsContract setPassword(string $password)
 * @method static string getPassword()
 * @method static MultiTenantMailerSettingsContract setEncryption(string $encryption)
 * @method static string getEncryption()
 * @method static MultiTenantMailerSettingsContract setFromName(string $name = null)
 * @method static null|string getFromName()
 * @method static MultiTenantMailerSettingsContract setFromAddress(string $addresses, string $name = null)
 * @method static null|string getFromAddress()
 *
 * @package Khaleejinfotech/MultiTenantMailer
 */
class MultiTenantMailerSettings extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return MultiTenantMailerSettingsContract::class;
    }
}

