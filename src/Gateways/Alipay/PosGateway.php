<?php

namespace Yansongda\Pay\Gateways\Alipay;

use Yansongda\Pay\Contracts\GatewayInterface;
use Yansongda\Supports\Config;

class PosGateway implements GatewayInterface
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
     * Pay a order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param array  $config_biz
     * @param string $scene
     *
     * @return array|bool
     */
    public function pay($endpoint, array $payload)
    {
        $config_biz['scene'] = $scene;

        return $this->getResult($config_biz, $this->getMethod());
    }

    /**
     * Get method config.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @return string
     */
    protected function getMethod()
    {
        return 'alipay.trade.pay';
    }

    /**
     * Get productCode config.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @return string
     */
    protected function getProductCode()
    {
        return 'FACE_TO_FACE_PAYMENT';
    }
}
