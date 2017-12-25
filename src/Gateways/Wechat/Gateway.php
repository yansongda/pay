<?php

namespace Yansongda\Pay\Gateways\Wechat;

use Yansongda\Pay\Contracts\GatewayInterface;
use Yansongda\Pay\Log;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Config;

abstract class Gateway implements GatewayInterface
{
    /**
     * Config.
     *
     * @var Config
     */
    protected $config;

    /**
     * Bootstrap.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Pay an order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $endpoint
     * @param array  $payload
     *
     * @return Collection
     */
    abstract public function pay($endpoint, array $payload);

    /**
     * Get trade type config.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @return string
     */
    abstract protected function getTradeType();

    /**
     * Preorder an order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param array $payload
     *
     * @return Collection
     */
    protected function preOrder($endpoint, $payload): Collection
    {
        $payload['sign'] = Support::generateSign($payload, $this->config->get('key'));

        Log::debug('Pre Order:', [$endpoint, $payload]);

        return Support::requestApi($endpoint, $payload, $this->config->get('key'));
    }
}
