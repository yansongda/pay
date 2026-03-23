<?php

declare(strict_types=1);

namespace Yansongda\Pay\Config;

class Config implements ConfigInterface
{
    public function __construct(
        public ?AlipayConfig $alipay = null,
        public ?WechatConfig $wechat = null,
        public ?UnipayConfig $unipay = null,
        public ?DouyinConfig $douyin = null,
        public ?JsbConfig $jsb = null,
        public ?LoggerConfig $logger = null,
        public ?HttpConfig $http = null,
        public array $additional = [],
    ) {
    }

    public function toArray(): array
    {
        $config = [];

        if ($this->alipay !== null) {
            $config['alipay'] = ['default' => $this->alipay->toArray()];
        }

        if ($this->wechat !== null) {
            $config['wechat'] = ['default' => $this->wechat->toArray()];
        }

        if ($this->unipay !== null) {
            $config['unipay'] = ['default' => $this->unipay->toArray()];
        }

        if ($this->douyin !== null) {
            $config['douyin'] = ['default' => $this->douyin->toArray()];
        }

        if ($this->jsb !== null) {
            $config['jsb'] = ['default' => $this->jsb->toArray()];
        }

        if ($this->logger !== null) {
            $config['logger'] = $this->logger->toArray();
        }

        if ($this->http !== null) {
            $config['http'] = $this->http->toArray();
        }

        return array_merge($config, $this->additional);
    }

    public static function fromArray(array $config): self
    {
        $alipay = null;
        if (isset($config['alipay']['default'])) {
            $alipay = AlipayConfig::fromArray($config['alipay']['default']);
        } elseif (isset($config['alipay']) && !isset($config['alipay']['default'])) {
            // Handle case where alipay config might be provided directly
            $firstKey = array_key_first($config['alipay']);
            if (is_array($config['alipay'][$firstKey])) {
                $alipay = AlipayConfig::fromArray($config['alipay'][$firstKey]);
            }
        }

        $wechat = null;
        if (isset($config['wechat']['default'])) {
            $wechat = WechatConfig::fromArray($config['wechat']['default']);
        } elseif (isset($config['wechat']) && !isset($config['wechat']['default'])) {
            $firstKey = array_key_first($config['wechat']);
            if (is_array($config['wechat'][$firstKey])) {
                $wechat = WechatConfig::fromArray($config['wechat'][$firstKey]);
            }
        }

        $unipay = null;
        if (isset($config['unipay']['default'])) {
            $unipay = UnipayConfig::fromArray($config['unipay']['default']);
        } elseif (isset($config['unipay']) && !isset($config['unipay']['default'])) {
            $firstKey = array_key_first($config['unipay']);
            if (is_array($config['unipay'][$firstKey])) {
                $unipay = UnipayConfig::fromArray($config['unipay'][$firstKey]);
            }
        }

        $douyin = null;
        if (isset($config['douyin']['default'])) {
            $douyin = DouyinConfig::fromArray($config['douyin']['default']);
        } elseif (isset($config['douyin']) && !isset($config['douyin']['default'])) {
            $firstKey = array_key_first($config['douyin']);
            if (is_array($config['douyin'][$firstKey])) {
                $douyin = DouyinConfig::fromArray($config['douyin'][$firstKey]);
            }
        }

        $jsb = null;
        if (isset($config['jsb']['default'])) {
            $jsb = JsbConfig::fromArray($config['jsb']['default']);
        } elseif (isset($config['jsb']) && !isset($config['jsb']['default'])) {
            $firstKey = array_key_first($config['jsb']);
            if (is_array($config['jsb'][$firstKey])) {
                $jsb = JsbConfig::fromArray($config['jsb'][$firstKey]);
            }
        }

        $logger = isset($config['logger']) ? LoggerConfig::fromArray($config['logger']) : null;
        $http = isset($config['http']) ? HttpConfig::fromArray($config['http']) : null;

        // Store additional config that doesn't match known structures
        $additional = [];
        $knownKeys = ['alipay', 'wechat', 'unipay', 'douyin', 'jsb', 'logger', 'http'];
        foreach ($config as $key => $value) {
            if (!in_array($key, $knownKeys)) {
                $additional[$key] = $value;
            }
        }

        return new self(
            alipay: $alipay,
            wechat: $wechat,
            unipay: $unipay,
            douyin: $douyin,
            jsb: $jsb,
            logger: $logger,
            http: $http,
            additional: $additional,
        );
    }
}
