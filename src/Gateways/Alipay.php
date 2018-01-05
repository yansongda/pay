<?php

namespace Yansongda\Pay\Gateways;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Yansongda\Pay\Contracts\GatewayApplicationInterface;
use Yansongda\Pay\Contracts\GatewayInterface;
use Yansongda\Pay\Exceptions\GatewayException;
use Yansongda\Pay\Exceptions\InvalidSignException;
use Yansongda\Pay\Gateways\Alipay\Support;
use Yansongda\Pay\Log;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Config;
use Yansongda\Supports\Str;

class Alipay implements GatewayApplicationInterface
{
    /**
     * Config.
     *
     * @var Config
     */
    protected $config;

    /**
     * Alipay payload.
     *
     * @var array
     */
    protected $payload;

    /**
     * Alipay gateway.
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
        $this->gateway = Support::baseUri($this->config->get('mode', 'normal'));
        $this->payload = [
            'app_id'      => $this->config->get('app_id'),
            'method'      => '',
            'format'      => 'JSON',
            'charset'     => 'utf-8',
            'sign_type'   => 'RSA2',
            'version'     => '1.0',
            'return_url'  => $this->config->get('return_url'),
            'notify_url'  => $this->config->get('notify_url'),
            'timestamp'   => date('Y-m-d H:i:s'),
            'sign'        => '',
            'biz_content' => '',
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
        $this->payload['biz_content'] = json_encode($params);

        $gateway = get_class($this).'\\'.Str::studly($gateway).'Gateway';

        if (class_exists($gateway)) {
            return $this->makePay($gateway);
        }

        throw new GatewayException("Pay Gateway [{$gateway}] not exists", 1);
    }

    /**
     * Verfiy sign.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @return Collection
     */
    public function verify(): Collection
    {
        $request = Request::createFromGlobals();

        $data = $request->request->count() > 0 ? $request->request->all() : $request->query->all();

        $data = Support::encoding($data, 'utf-8', $data['charset'] ?? 'gb2312');

        Log::debug('Receive Alipay Request:', $data);

        if (Support::verifySign($data, $this->config->get('ali_public_key'))) {
            return new Collection($data);
        }

        Log::warning('Alipay Sign Verify FAILED', $data);

        throw new InvalidSignException('Alipay Sign Verify FAILED', 3, $data);
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
        $this->payload['method'] = 'alipay.trade.query';
        $this->payload['biz_content'] = json_encode(is_array($order) ? $order : ['out_trade_no' => $order]);
        $this->payload['sign'] = Support::generateSign($this->payload, $this->config->get('private_key'));

        Log::debug('Find An Order:', [$this->gateway, $this->payload]);

        return Support::requestApi($this->payload, $this->config->get('ali_public_key'));
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
        $this->payload['method'] = 'alipay.trade.refund';
        $this->payload['biz_content'] = json_encode($order);
        $this->payload['sign'] = Support::generateSign($this->payload, $this->config->get('private_key'));

        Log::debug('Refund An Order:', [$this->gateway, $this->payload]);

        return Support::requestApi($this->payload, $this->config->get('ali_public_key'));
    }

    /**
     * Cancel an order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string|array $order
     *
     * @return Collection
     */
    public function cancel($order): Collection
    {
        $this->payload['method'] = 'alipay.trade.cancel';
        $this->payload['biz_content'] = json_encode(is_array($order) ? $order : ['out_trade_no' => $order]);
        $this->payload['sign'] = Support::generateSign($this->payload, $this->config->get('private_key'));

        Log::debug('Cancel An Order:', [$this->gateway, $this->payload]);

        return Support::requestApi($this->payload, $this->config->get('ali_public_key'));
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
    public function close($order): Collection
    {
        $this->payload['method'] = 'alipay.trade.close';
        $this->payload['biz_content'] = json_encode(is_array($order) ? $order : ['out_trade_no' => $order]);
        $this->payload['sign'] = Support::generateSign($this->payload, $this->config->get('private_key'));

        Log::debug('Close An Order:', [$this->gateway, $this->payload]);

        return Support::requestApi($this->payload, $this->config->get('ali_public_key'));
    }

    /**
     * Reply success to alipay.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @return Response
     */
    public function success(): Response
    {
        return Response::create('success');
    }

    /**
     * Make pay gateway.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $gateway
     *
     * @return Response|Collection
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
     * @param array  $params
     *
     * @return Response|Collection
     */
    public function __call($method, $params)
    {
        return $this->pay($method, ...$params);
    }
}
