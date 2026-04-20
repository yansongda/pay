<?php

declare(strict_types=1);

namespace Yansongda\Pay;

use Closure;
use Psr\Container\ContainerInterface;
use Yansongda\Artful\Artful;
use Yansongda\Artful\Event\ArtfulEnd;
use Yansongda\Artful\Event\ArtfulStart;
use Yansongda\Artful\Event\HttpEnd;
use Yansongda\Artful\Event\HttpStart;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Pay\Config\ProviderConfigInterface;
use Yansongda\Pay\Provider\Alipay;
use Yansongda\Pay\Provider\Douyin;
use Yansongda\Pay\Provider\Jsb;
use Yansongda\Pay\Provider\Paypal;
use Yansongda\Pay\Provider\Stripe;
use Yansongda\Pay\Provider\Unipay;
use Yansongda\Pay\Provider\Wechat;
use Yansongda\Pay\Service\AlipayServiceProvider;
use Yansongda\Pay\Service\DouyinServiceProvider;
use Yansongda\Pay\Service\JsbServiceProvider;
use Yansongda\Pay\Service\PaypalServiceProvider;
use Yansongda\Pay\Service\StripeServiceProvider;
use Yansongda\Pay\Service\UnipayServiceProvider;
use Yansongda\Pay\Service\WechatServiceProvider;

/**
 * @method static Alipay alipay(array $config = [], $container = null)
 * @method static Wechat wechat(array $config = [], $container = null)
 * @method static Unipay unipay(array $config = [], $container = null)
 * @method static Jsb    jsb(array $config = [], $container = null)
 * @method static Douyin douyin(array $config = [], $container = null)
 * @method static Paypal paypal(array $config = [], $container = null)
 * @method static Stripe stripe(array $config = [], $container = null)
 */
class Pay
{
    /**
     * Provider 名称常量.
     */
    public const PROVIDER_WECHAT = 'wechat';
    public const PROVIDER_ALIPAY = 'alipay';
    public const PROVIDER_UNIPAY = 'unipay';
    public const PROVIDER_JSB = 'jsb';
    public const PROVIDER_DOUYIN = 'douyin';
    public const PROVIDER_PAYPAL = 'paypal';
    public const PROVIDER_STRIPE = 'stripe';

    /**
     * 正常模式.
     */
    public const MODE_NORMAL = 0;

    /**
     * 沙箱模式.
     */
    public const MODE_SANDBOX = 1;

    /**
     * 服务商模式.
     */
    public const MODE_SERVICE = 2;

    protected static array $providers = [
        AlipayServiceProvider::class,
        WechatServiceProvider::class,
        UnipayServiceProvider::class,
        JsbServiceProvider::class,
        DouyinServiceProvider::class,
        PaypalServiceProvider::class,
        StripeServiceProvider::class,
    ];

    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public static function __callStatic(string $service, array $config = [])
    {
        if (!empty($config)) {
            self::config(...$config);
        }

        return Artful::get($service);
    }

    /**
     * @throws ContainerException
     */
    public static function config(array|Config $config = [], Closure|ContainerInterface|null $container = null): bool
    {
        if (is_array($config) && Artful::hasContainer() && !($config['_force'] ?? false)) {
            return false;
        }

        $configObject = is_array($config) ? new Config(self::mergeConfig($config)) : $config;
        $result = Artful::config(self::exportConfig($configObject->all()), $container);

        if ($result) {
            Artful::set(Config::class, $configObject);
            Event::addListener(ArtfulStart::class, [PayListener::class, 'artfulStart']);
            Event::addListener(ArtfulEnd::class, [PayListener::class, 'artfulEnd']);
            Event::addListener(HttpStart::class, [PayListener::class, 'httpStart']);
            Event::addListener(HttpEnd::class, [PayListener::class, 'httpEnd']);
        }

        foreach (self::$providers as $provider) {
            Artful::load($provider);
        }

        return $result;
    }

    private static function mergeConfig(array $config): array
    {
        if (!($config['_force'] ?? false) || !Artful::hasContainer()) {
            return $config;
        }

        try {
            $current = self::get(Config::class);
        } catch (ContainerException|ServiceNotFoundException) {
            return $config;
        }

        return array_replace_recursive(self::exportConfig($current->all()), $config);
    }

    private static function exportConfig(mixed $config): mixed
    {
        if ($config instanceof ProviderConfigInterface) {
            return self::filterNullConfigValues($config->toArray());
        }

        if (!is_array($config)) {
            return $config;
        }

        foreach ($config as $key => $value) {
            $config[$key] = self::exportConfig($value);
        }

        return $config;
    }

    private static function filterNullConfigValues(array $config): array
    {
        foreach ($config as $key => $value) {
            if (null === $value) {
                unset($config[$key]);

                continue;
            }

            if (is_array($value)) {
                $config[$key] = self::filterNullConfigValues($value);
            }
        }

        return $config;
    }

    /**
     * @throws ContainerException
     */
    public static function set(string $name, mixed $value): void
    {
        Artful::set($name, $value);
    }

    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public static function get(string $service): mixed
    {
        return Artful::get($service);
    }

    public static function setContainer(Closure|ContainerInterface|null $container): void
    {
        Artful::setContainer($container);
    }

    public static function clear(): void
    {
        Artful::clear();
    }
}
