<?php

namespace Yansongda\Pay\Gateways;

use Symfony\Component\HttpFoundation\Response;
use Yansongda\Pay\Contracts\GatewayApplicationInterface;
use Yansongda\Pay\Contracts\GatewayInterface;
use Yansongda\Pay\Exceptions\GatewayException;
use Yansongda\Pay\Gateways\Wechat\Support;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Config;
use Yansongda\Supports\Str;

class Wechat implements GatewayApplicationInterface
{
    /**
     * Config.
     *
     * @var Config
     */
    protected $config;

    /**
     * Wechat payload.
     *
     * @var array
     */
    protected $payload;

    /**
     * Wechat gateway.
     *
     * @var string
     */
    protected $gateway;

    /**
     * Bootstrap.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->gateway = Support::baseUri();
        $this->config = $config;
        $this->payload = [
            'appid'      => $this->config->get('app_id', ''),
            'mch_id'     => $this->config->get('mch_id', ''),
            'nonce_str'  => Str::random(),
            'sign_type'  => 'MD5',
            'notify_url' => $this->config->get('notify_url', ''),
            'trade_type' => '',
        ];
    }

    /**
     * Pay an order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $gateway
     * @param array  $params
     *
     * @return Response|Collection
     */
    public function pay($gateway, $params = [])
    {
        $gateway = get_class($this) . '\\' . Str::studly($gateway) . 'Gateway';
        
        if (class_exists($gateway)) {
            return $this->makePay($gateway);
        }

        throw new GatewayException("Pay Gateway [{$gateway}] Not Exists", 1);
    }

    public function verify()
    {
        # code...
    }

    public function find($order)
    {
        # code...
    }

    public function refund(array $order)
    {
        # code...
    }

    public function cancel($order)
    {
        # code...
    }

    public function close($order)
    {
        # code...
    }

    /**
     * Echo success to server.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @return Response
     */
    public function success(): Response
    {
        return Response::create(
            Support::toXml(['return_code' => 'SUCCESS']),
            200,
            ['Content-Type' => 'application/xml']
        );
    }

    /**
     * Make pay gateway.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $gateway
     *
     * @return Response
     */
    protected function makePay($gateway)
    {
        $app = new $gateway($this->config);

        if ($app instanceof GatewayInterface) {
            return $app->pay($this->gateway, $this->payload);
        }

        throw new GatewayException("Pay Gateway [{$gateway}] Must Be An Instance Of GatewayInterface", 2);
    }

    /**
     * Magic pay.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $method
     * @param string $params
     *
     * @return Response|Collection
     */
    public function __call($method, $params)
    {
        return self::pay($method, $params);
    }
}
