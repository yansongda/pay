<?php

namespace Yansongda\Pay\Gateways;

use Symfony\Component\HttpFoundation\Response;
use Yansongda\Pay\Contracts\GatewayApplicationInterface;
use Yansongda\Pay\Contracts\GatewayInterface;
use Yansongda\Pay\Exceptions\InvalidConfigException;
use Yansongda\Pay\Log;
use Yansongda\Supports\Config;
use Yansongda\Supports\Str;
use Yansongda\Supports\Traits\HasHttpRequest;

class Alipay implements GatewayApplicationInterface
{
    use HasHttpRequest;

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
    protected $baseUri = 'https://openapi.alipaydev.com/gateway.do?charset=utf-8';

    /**
     * Bootstrap.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        if (is_null($config->get('app_id'))) {
            throw new InvalidConfigException('Missing Alipay Config -- [app_id]');
        }

        $this->config = $config;
        $this->payload = [
            'app_id'      => $this->config->get('app_id'),
            'method'      => '',
            'format'      => 'JSON',
            'charset'     => 'utf-8',
            'sign_type'   => 'RSA2',
            'version'     => '1.0',
            'return_url'  => $this->config->get('return_url', ''),
            'notify_url'  => $this->config->get('notify_url', ''),
            'timestamp'   => date('Y-m-d H:i:s'),
            'sign'        => '',
            'biz_content' => '',
        ];
    }

    /**
     * Pay a order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $gateway
     * @param array $params
     *
     * @return Response
     */
    public function pay($gateway, $params = [])
    {
        $this->payload['biz_content'] = json_encode($params);

        $gateway = get_class($this) . "\\" . Str::studly($gateway) . "Gateway";
        
        if (class_exists($gateway)) {
            return $this->makePay($gateway);
        }

        throw new GatewayException("Pay Gateway [{$gateway}] not exists", 1);
    }

    public function verify($data = null)
    {
        if (is_null($this->user_config->get('ali_public_key'))) {
            throw new InvalidArgumentException('Missing Config -- [ali_public_key]');
        }

        $sign = is_null($sign) ? $data['sign'] : $sign;

        $res = "-----BEGIN PUBLIC KEY-----\n".
                wordwrap($this->user_config->get('ali_public_key'), 64, "\n", true).
                "\n-----END PUBLIC KEY-----";

        $toVerify = $sync ? json_encode($data) : $this->getSignContent($data, true);

        return openssl_verify($toVerify, base64_decode($sign), $res, OPENSSL_ALGO_SHA256) === 1 ? $data : false;
    }

    public function find()
    {
        # code...
    }

    public function refund()
    {
        # code...
    }

    public function cancel()
    {
        # code...
    }

    public function close()
    {
        # code...
    }

    /**
     * Reply success to alipay.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @return Response
     */
    public function success()
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
     * @return Response
     */
    protected function makePay($gateway)
    {
        $app = new $gateway($this->config);

        if ($app instanceof GatewayInterface) {
            return $app->pay($this->baseUri, $this->payload);
        }

        throw new GatewayException("Pay Gateway [{$gateway}] must be a instance of GatewayInterface", 2);
    }

    /**
     * Magic pay.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $method
     * @param array $params
     *
     * @return [type]
     */
    public function __call($method, $params)
    {
        return $this->pay($method, ...$params);
    }
}
