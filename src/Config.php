<?php

declare(strict_types=1);

namespace Yansongda\Pay;

use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Pay\Config\AirwallexConfig;
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
     * Provider 名单.
     */
    private const PROVIDERS = [
        Pay::PROVIDER_WECHAT,
        Pay::PROVIDER_ALIPAY,
        Pay::PROVIDER_AIRWALLEX,
        Pay::PROVIDER_UNIPAY,
        Pay::PROVIDER_JSB,
        Pay::PROVIDER_DOUYIN,
        Pay::PROVIDER_PAYPAL,
        Pay::PROVIDER_STRIPE,
    ];

    /**
     * @param array<string, mixed> $items
     */
    public function __construct(array $items = [])
    {
        parent::__construct($items);

        // 转换 Provider 配置为对象（支付宝按 version 拆分为 V2/V3 配置类，走工厂分派）
        foreach (self::PROVIDERS as $provider) {
            foreach ($this->items[$provider] ?? [] as $tenant => $config) {
                if (is_array($config)) {
                    $this->items[$provider][$tenant] = match ($provider) {
                        Pay::PROVIDER_WECHAT => new WechatConfig($config, $tenant),
                        Pay::PROVIDER_ALIPAY => AlipayConfig::fromArray($config, $tenant),
                        Pay::PROVIDER_AIRWALLEX => new AirwallexConfig($config, $tenant),
                        Pay::PROVIDER_UNIPAY => new UnipayConfig($config, $tenant),
                        Pay::PROVIDER_JSB => new JsbConfig($config, $tenant),
                        Pay::PROVIDER_DOUYIN => new DouyinConfig($config, $tenant),
                        Pay::PROVIDER_PAYPAL => new PaypalConfig($config, $tenant),
                        Pay::PROVIDER_STRIPE => new StripeConfig($config, $tenant),
                    };
                }
            }
        }
    }

    /**
     * 获取指定 Provider 的配置对象.
     *
     * @throws InvalidConfigException 当 Provider 或租户配置不存在时
     */
    public function getProviderConfig(string $provider, ?string $tenant = null): ProviderConfigInterface
    {
        if (!in_array($provider, self::PROVIDERS, true)) {
            throw new InvalidConfigException(Exception::CONFIG_PROVIDER_INVALID, "配置异常: 未知的 Provider - {$provider}");
        }

        $tenant = $tenant ?? 'default';

        if (!isset($this->items[$provider][$tenant])) {
            throw new InvalidConfigException(Exception::CONFIG_PROVIDER_INVALID, "配置异常: {$provider}.{$tenant} 配置不存在");
        }

        $config = $this->items[$provider][$tenant];
        $config->validate();

        return $config;
    }
}
