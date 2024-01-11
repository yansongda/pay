<?php

declare(strict_types=1);

namespace Yansongda\Pay;

use Closure;
use Psr\Container\ContainerInterface;
use Yansongda\Artful\Artful;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Pay\Provider\Alipay;
use Yansongda\Pay\Provider\Unipay;
use Yansongda\Pay\Provider\Wechat;
use Yansongda\Pay\Service\AlipayServiceProvider;
use Yansongda\Pay\Service\UnipayServiceProvider;
use Yansongda\Pay\Service\WechatServiceProvider;

/**
 * @method static Alipay alipay(array $config = [], $container = null)
 * @method static Wechat wechat(array $config = [], $container = null)
 * @method static Unipay unipay(array $config = [], $container = null)
 */
class Pay
{
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
    public static function config(array $config = [], Closure|ContainerInterface $container = null): bool
    {
        $result = Artful::config($config, $container);

        foreach (self::$providers as $provider) {
            Artful::load($provider);
        }

        return $result;
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

    public static function setContainer(null|Closure|ContainerInterface $container): void
    {
        Artful::setContainer($container);
    }

    public static function clear(): void
    {
        Artful::clear();
    }
}
