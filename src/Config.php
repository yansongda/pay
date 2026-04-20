<?php

declare(strict_types=1);

namespace Yansongda\Pay;

use Yansongda\Pay\Config\AlipayConfig;
use Yansongda\Pay\Config\DouyinConfig;
use Yansongda\Pay\Config\JsbConfig;
use Yansongda\Pay\Config\PaypalConfig;
use Yansongda\Pay\Config\ProviderConfigInterface;
use Yansongda\Pay\Config\StripeConfig;
use Yansongda\Pay\Config\UnipayConfig;
use Yansongda\Pay\Config\WechatConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Supports\Config as BaseConfig;

class Config extends BaseConfig
{
    /**
     * Provider 配置类映射.
     */
    private const PROVIDER_CONFIG_MAP = [
        'wechat' => WechatConfig::class,
        'alipay' => AlipayConfig::class,
        'unipay' => UnipayConfig::class,
        'jsb' => JsbConfig::class,
        'douyin' => DouyinConfig::class,
        'paypal' => PaypalConfig::class,
        'stripe' => StripeConfig::class,
    ];

    public function __construct(array $items = [])
    {
        parent::__construct($items);

        // 转换 Provider 配置为对象
        foreach (self::PROVIDER_CONFIG_MAP as $provider => $configClass) {
            if (isset($this->items[$provider])) {
                foreach ($this->items[$provider] as $tenant => $config) {
                    if (is_array($config)) {
                        $this->items[$provider][$tenant] = new $configClass($config, $tenant);
                    }
                }
            }
        }
    }

    /**
     * 获取指定 Provider 的配置对象.
     *
     * @throws Exception 当 Provider 或租户配置不存在时
     */
    public function getProviderConfig(string $provider, ?string $tenant = null): ProviderConfigInterface
    {
        if (!isset(self::PROVIDER_CONFIG_MAP[$provider])) {
            throw new Exception("Unknown provider: {$provider}");
        }

        $tenant = $tenant ?? 'default';

        if (!isset($this->items[$provider][$tenant])) {
            throw new Exception("Config for {$provider}.{$tenant} not found");
        }

        return $this->items[$provider][$tenant];
    }
}
