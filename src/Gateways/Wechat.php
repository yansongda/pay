<?php

namespace Hanwenbo\Pay\Gateways;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Hanwenbo\Pay\Contracts\GatewayApplicationInterface;
use Hanwenbo\Pay\Contracts\GatewayInterface;
use Hanwenbo\Pay\Exceptions\GatewayException;
use Hanwenbo\Pay\Exceptions\InvalidSignException;
use Hanwenbo\Pay\Gateways\Wechat\Support;
use Hanwenbo\Pay\Log;
use Hanwenbo\Supports\Collection;
use Hanwenbo\Supports\Config;
use Hanwenbo\Supports\Str;

/**
 * @method \Hanwenbo\Pay\Gateways\Wechat\AppGateway app(array $config) APP 支付
 * @method \Hanwenbo\Pay\Gateways\Wechat\GroupRedpackGateway groupRedpack(array $config) 分裂红包
 * @method \Hanwenbo\Pay\Gateways\Wechat\MiniappGateway miniapp(array $config) 小程序支付
 * @method \Hanwenbo\Pay\Gateways\Wechat\MpGateway mp(array $config) 公众号支付
 * @method \Hanwenbo\Pay\Gateways\Wechat\PosGateway pos(array $config) 刷卡支付
 * @method \Hanwenbo\Pay\Gateways\Wechat\RedpackGateway redpack(array $config) 普通红包
 * @method \Hanwenbo\Pay\Gateways\Wechat\ScanGateway scan(array $config) 扫码支付
 * @method \Hanwenbo\Pay\Gateways\Wechat\TransferGateway transfer(array $config) 企业付款
 * @method \Hanwenbo\Pay\Gateways\Wechat\WapGateway wap(array $config) H5 支付
 */
class Wechat implements GatewayApplicationInterface
{
    const MODE_NORMAL = 'normal'; // 普通模式
    const MODE_DEV = 'dev'; // 沙箱模式
    const MODE_HK = 'hk'; // 香港钱包
    const MODE_SERVICE = 'service'; // 服务商

    /**
     * Config.
     *
     * @var Config
     */
    protected $config;

    /**
     * Mode.
     *
     * @var string
     */
    protected $mode;

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
        $this->config = $config;
        $this->mode = $this->config->get('mode', self::MODE_NORMAL);
        $this->gateway = Support::baseUri($this->mode);
        $this->payload = [
            'appid'            => $this->config->get('app_id', ''),
            'mch_id'           => $this->config->get('mch_id', ''),
            'nonce_str'        => Str::random(),
            'notify_url'       => $this->config->get('notify_url', ''),
            'sign'             => '',
            'trade_type'       => '',
            'spbill_create_ip' => Request::createFromGlobals()->getClientIp(),
        ];

        if ($this->mode === static::MODE_SERVICE) {
            $this->payload = array_merge($this->payload, [
                'sub_mch_id' => $this->config->get('sub_mch_id'),
                'sub_appid'  => $this->config->get('sub_app_id', ''),
            ]);
        }
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
        $this->payload = array_merge($this->payload, $params);

        $gateway = get_class($this).'\\'.Str::studly($gateway).'Gateway';

        if (class_exists($gateway)) {
            return $this->makePay($gateway);
        }

        throw new GatewayException("Pay Gateway [{$gateway}] Not Exists", 1);
    }

    /**
     * Verify data.
     * @param string $content
     * @author yansongda <me@yansongda.cn>
     * @return Collection
     */
    public function verify(string $content = null): Collection
    {
        $request = Request::createFromGlobals();

        $data = Support::fromXml($content ? $content : $request->getContent());

        Log::debug('Receive Wechat Request:', $data);

        if (Support::generateSign($data, $this->config->get('key')) === $data['sign']) {
            return new Collection($data);
        }

        Log::warning('Wechat Sign Verify FAILED', $data);

        throw new InvalidSignException('Wechat Sign Verify FAILED', 3, $data);
    }

    /**
     * Query an order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string|array $order
     *
     * @return Collection
     */
    public function find($order): Collection
    {
        $this->payload = Support::filterPayload($this->payload, $order, $this->config);

        return Support::requestApi('pay/orderquery', $this->payload, $this->config->get('key'));
    }

    /**
     * Refund an order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param array $order
     *
     * @return Collection
     */
    public function refund($order): Collection
    {
        $this->payload = Support::filterPayload($this->payload, $order, $this->config);

        return Support::requestApi(
            'secapi/pay/refund',
            $this->payload,
            $this->config->get('key'),
            $this->config->get('cert_client'),
            $this->config->get('cert_key')
        );
    }

    /**
     * Cancel an order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param array $order
     *
     * @return Collection
     */
    public function cancel($order): Collection
    {
        throw new GatewayException('Wechat Do Not Have Cancel API! Plase use Close API!', 3);
    }

    /**
     * Close an order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string|array $order
     *
     * @return Collection
     */
    public function close($order)
    {
        unset($this->payload['spbill_create_ip']);

        $this->payload = Support::filterPayload($this->payload, $order, $this->config);

        return Support::requestApi('pay/closeorder', $this->payload, $this->config->get('key'));
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
        return self::pay($method, ...$params);
    }
}
