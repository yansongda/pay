<?php

declare(strict_types=1);

namespace Yansongda\Pay;

use Closure;
use Psr\Container\ContainerInterface;
use Yansongda\Artful\Artful;
use Yansongda\Artful\Exception\ContainerException;
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
class Pay extends Artful
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

    protected array $providers = [
        AlipayServiceProvider::class,
        WechatServiceProvider::class,
        UnipayServiceProvider::class,
    ];

    /**
     * @throws ContainerException
     */
    public static function config(array $config = [], Closure|ContainerInterface $container = null): bool
    {
        $result = parent::config($config, $container);

        foreach ($this->providers as $provider) {
            self::load($provider);
        }

        return $result;
    }
}
