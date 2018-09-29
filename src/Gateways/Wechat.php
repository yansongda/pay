<?php

namespace Yansongda\Pay\Gateways;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Yansongda\Pay\Contracts\GatewayApplicationInterface;
use Yansongda\Pay\Contracts\GatewayInterface;
use Yansongda\Pay\Exceptions\GatewayException;
use Yansongda\Pay\Exceptions\InvalidGatewayException;
use Yansongda\Pay\Exceptions\InvalidSignException;
use Yansongda\Pay\Gateways\Wechat\Support;
use Yansongda\Pay\Log;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Config;
use Yansongda\Supports\Str;

/**
 * @method Response app(array $config) APP 支付
 * @method Collection groupRedpack(array $config) 分裂红包
 * @method Collection miniapp(array $config) 小程序支付
 * @method Collection mp(array $config) 公众号支付
 * @method Collection pos(array $config) 刷卡支付
 * @method Collection redpack(array $config) 普通红包
 * @method Collection scan(array $config) 扫码支付
 * @method Collection transfer(array $config) 企业付款
 * @method Response wap(array $config) H5 支付
 */
class Wechat implements GatewayApplicationInterface
{
    /**
     * 普通模式.
     */
    const MODE_NORMAL = 'normal';

    /**
     * 沙箱模式.
     */
    const MODE_DEV = 'dev';

    /**
     * 香港钱包 API.
     */
    const MODE_HK = 'hk';

    /**
     * 服务商模式.
     */
    const MODE_SERVICE = 'service';

    /**
     * Const url.
     */
    const URL = [
        self::MODE_NORMAL  => 'https://api.mch.weixin.qq.com/',
        self::MODE_DEV     => 'https://api.mch.weixin.qq.com/sandboxnew/',
        self::MODE_HK      => 'https://apihk.mch.weixin.qq.com/',
        self::MODE_SERVICE => 'https://api.mch.weixin.qq.com/',
    ];

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
     *
     * @throws \Exception
     */
    public function __construct(Config $config)
    {
        $this->gateway = Support::getInstance($config)->getBaseUri();
        $this->payload = [
            'appid'            => $config->get('app_id', ''),
            'mch_id'           => $config->get('mch_id', ''),
            'nonce_str'        => Str::random(),
            'notify_url'       => $config->get('notify_url', ''),
            'sign'             => '',
            'trade_type'       => '',
            'spbill_create_ip' => (php_sapi_name() == 'cli' ? request() : Request::createFromGlobals())->getClientIp(),
        ];

        if ($config->get('mode', self::MODE_NORMAL) === static::MODE_SERVICE) {
            $this->payload = array_merge($this->payload, [
                'sub_mch_id' => $config->get('sub_mch_id'),
                'sub_appid'  => $config->get('sub_app_id', ''),
            ]);
        }
    }

    /**
     * Magic pay.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $method
     * @param string $params
     *
     * @throws InvalidGatewayException
     *
     * @return Response|Collection
     */
    public function __call($method, $params)
    {
        return self::pay($method, ...$params);
    }

    /**
     * Pay an order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $gateway
     * @param array  $params
     *
     * @throws InvalidGatewayException
     *
     * @return Response|Collection
     */
    public function pay($gateway, $params = [])
    {
        Log::debug('Starting To Wechat', [$gateway, $params]);

        $this->payload = array_merge($this->payload, $params);

        $gateway = get_class($this).'\\'.Str::studly($gateway).'Gateway';

        if (class_exists($gateway)) {
            return $this->makePay($gateway);
        }

        throw new InvalidGatewayException("Pay Gateway [{$gateway}] Not Exists");
    }

    /**
     * Verify data.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string|null $content
     * @param bool        $refund
     *
     * @throws InvalidSignException
     * @throws \Yansongda\Pay\Exceptions\InvalidArgumentException
     *
     * @return Collection
     */
    public function verify($content = null, $refund = false): Collection
    {
        $content = $content ?? (php_sapi_name() == 'cli' ? request() : Request::createFromGlobals())->getContent();

        Log::info('Received Wechat Request', [$content]);

        $data = Support::fromXml($content);
        if ($refund) {
            $decrypt_data = Support::decryptRefundContents($data['req_info']);
            $data = array_merge(Support::fromXml($decrypt_data), $data);
        }

        Log::debug('Resolved The Received Wechat Request Data', $data);

        if ($refund || Support::generateSign($data) === $data['sign']) {
            return new Collection($data);
        }

        Log::warning('Wechat Sign Verify FAILED', $data);

        throw new InvalidSignException('Wechat Sign Verify FAILED', $data);
    }

    /**
     * Query an order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string|array $order
     * @param bool         $refund
     *
     * @throws GatewayException
     * @throws InvalidSignException
     * @throws \Yansongda\Pay\Exceptions\InvalidArgumentException
     *
     * @return Collection
     */
    public function find($order, $refund = false): Collection
    {
        $this->payload = Support::filterPayload($this->payload, $order);

        Log::info('Starting To Find An Wechat Order', [$this->gateway, $this->payload]);

        return Support::requestApi(
            $refund ? 'pay/refundquery' : 'pay/orderquery',
            $this->payload
        );
    }

    /**
     * Refund an order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param array $order
     *
     * @throws GatewayException
     * @throws InvalidSignException
     * @throws \Yansongda\Pay\Exceptions\InvalidArgumentException
     *
     * @return Collection
     */
    public function refund($order): Collection
    {
        $this->payload = Support::filterPayload($this->payload, $order, true);

        Log::info('Starting To Refund An Wechat Order', [$this->gateway, $this->payload]);

        return Support::requestApi(
            'secapi/pay/refund',
            $this->payload,
            true
        );
    }

    /**
     * Cancel an order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param array $order
     *
     * @throws GatewayException
     *
     * @return Collection
     */
    public function cancel($order): Collection
    {
        Log::warning('Using Not Exist Wechat Cancel API', $order);

        throw new GatewayException('Wechat Do Not Have Cancel API! Please use Close API!');
    }

    /**
     * Close an order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string|array $order
     *
     * @throws GatewayException
     * @throws InvalidSignException
     * @throws \Yansongda\Pay\Exceptions\InvalidArgumentException
     *
     * @return Collection
     */
    public function close($order): Collection
    {
        unset($this->payload['spbill_create_ip']);

        $this->payload = Support::filterPayload($this->payload, $order);

        Log::info('Starting To Close An Wechat Order', [$this->gateway, $this->payload]);

        return Support::requestApi('pay/closeorder', $this->payload);
    }

    /**
     * Echo success to server.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @throws \Yansongda\Pay\Exceptions\InvalidArgumentException
     *
     * @return Response
     */
    public function success(): Response
    {
        return Response::create(
            Support::toXml(['return_code' => 'SUCCESS', 'return_msg' => 'OK']),
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
     * @throws InvalidGatewayException
     *
     * @return Response|Collection
     */
    protected function makePay($gateway)
    {
        $app = new $gateway();

        if ($app instanceof GatewayInterface) {
            return $app->pay($this->gateway, $this->payload);
        }

        throw new InvalidGatewayException("Pay Gateway [{$gateway}] Must Be An Instance Of GatewayInterface");
    }
}
