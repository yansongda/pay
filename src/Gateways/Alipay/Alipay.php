<?php

namespace Yansongda\Pay\Gateways\Alipay;

use Yansongda\Pay\Contracts\GatewayInterface;
use Yansongda\Pay\Exceptions\GatewayException;
use Yansongda\Pay\Exceptions\InvalidArgumentException;
use Yansongda\Pay\Support\Config;
use Yansongda\Pay\Traits\HasHttpRequest;

abstract class Alipay implements GatewayInterface
{
    use HasHttpRequest;

    /**
     * @var string
     */
    protected $gateway = 'https://openapi.alipay.com/gateway.do'.'?charset=utf-8';

    /**
     * alipay global config params.
     *
     * @var array
     */
    protected $config;

    /**
     * user's config params.
     *
     * @var \Yansongda\Pay\Support\Config
     */
    protected $user_config;

    /**
     * [__construct description].
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param array $config [description]
     */
    public function __construct(array $config)
    {
        $this->user_config = new Config($config);

        if (is_null($this->user_config->get('app_id'))) {
            throw new InvalidArgumentException('Missing Config -- [app_id]');
        }

        $this->config = [
            'app_id'      => $this->user_config->get('app_id'),
            'method'      => '',
            'format'      => 'JSON',
            'charset'     => 'utf-8',
            'sign_type'   => 'RSA2',
            'version'     => '1.0',
            'return_url'  => $this->user_config->get('return_url', ''),
            'notify_url'  => $this->user_config->get('notify_url', ''),
            'timestamp'   => date('Y-m-d H:i:s'),
            'sign'        => '',
            'biz_content' => '',
        ];
    }

    /**
     * pay a order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param array $config_biz
     *
     * @return mixed
     */
    public function pay(array $config_biz)
    {
        $config_biz['product_code'] = $this->getProductCode();

        $this->config['method'] = $this->getMethod();
        $this->config['biz_content'] = json_encode($config_biz);
        $this->config['sign'] = $this->getSign();
    }

    /**
     * refund a order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param mixed $config_biz
     *
     * @return array|bool
     */
    public function refund($config_biz, $refund_amount = null)
    {
        if (!is_array($config_biz)) {
            $config_biz = [
                'out_trade_no'  => $config_biz,
                'refund_amount' => $refund_amount,
            ];
        }

        return $this->getResult($config_biz, 'alipay.trade.refund');
    }

    /**
     * close a order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param array|string $config_biz
     *
     * @return array|bool
     */
    public function close($config_biz)
    {
        if (!is_array($config_biz)) {
            $config_biz = [
                'out_trade_no' => $config_biz,
            ];
        }

        return $this->getResult($config_biz, 'alipay.trade.close');
    }

    /**
     * find a order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $out_trade_no
     *
     * @return array|bool
     */
    public function find($out_trade_no = '')
    {
        $config_biz = [
            'out_trade_no' => $out_trade_no,
        ];

        return $this->getResult($config_biz, 'alipay.trade.query');
    }

    /**
     * verify the notify.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param array  $data
     * @param string $sign
     * @param bool   $sync
     *
     * @return array|bool
     */
    public function verify($data, $sign = null, $sync = false)
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

    /**
     * get method config.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @return string
     */
    abstract protected function getMethod();

    /**
     * get productCode config.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @return string
     */
    abstract protected function getProductCode();

    /**
     * build pay html.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @return string
     */
    protected function buildPayHtml()
    {
        $sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='".$this->gateway."' method='POST'>";
        while (list($key, $val) = each($this->config)) {
            $val = str_replace("'", '&apos;', $val);
            $sHtml .= "<input type='hidden' name='".$key."' value='".$val."'/>";
        }
        $sHtml .= "<input type='submit' value='ok' style='display:none;''></form>";
        $sHtml .= "<script>document.forms['alipaysubmit'].submit();</script>";

        return $sHtml;
    }

    /**
     * get alipay api result.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param array  $config_biz
     * @param string $method
     *
     * @return array|bool
     */
    protected function getResult($config_biz, $method)
    {
        $this->config['biz_content'] = json_encode($config_biz);
        $this->config['method'] = $method;
        $this->config['sign'] = $this->getSign();

        $method = str_replace('.', '_', $method).'_response';

        $data = json_decode($this->post($this->gateway, $this->config), true);

        if (!isset($data[$method]['code']) || $data[$method]['code'] !== '10000') {
            throw new GatewayException(
                'get result error:'.$data[$method]['msg'].' - '.$data[$method]['sub_code'],
                $data[$method]['code'],
                $data);
        }

        return $this->verify($data[$method], $data['sign'], true);
    }

    /**
     * get sign.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @return string
     */
    protected function getSign()
    {
        if (is_null($this->user_config->get('private_key'))) {
            throw new InvalidArgumentException('Missing Config -- [private_key]');
        }

        $res = "-----BEGIN RSA PRIVATE KEY-----\n".
                wordwrap($this->user_config->get('private_key'), 64, "\n", true).
                "\n-----END RSA PRIVATE KEY-----";

        openssl_sign($this->getSignContent($this->config), $sign, $res, OPENSSL_ALGO_SHA256);

        return base64_encode($sign);
    }

    /**
     * get signContent that is to be signed.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param array $toBeSigned
     * @param bool  $verify
     *
     * @return string
     */
    protected function getSignContent(array $toBeSigned, $verify = false)
    {
        ksort($toBeSigned);

        $stringToBeSigned = '';
        foreach ($toBeSigned as $k => $v) {
            if ($verify && $k != 'sign' && $k != 'sign_type') {
                $stringToBeSigned .= $k.'='.$v.'&';
            }
            if (!$verify && $v !== '' && !is_null($v) && $k != 'sign' && '@' != substr($v, 0, 1)) {
                $stringToBeSigned .= $k.'='.$v.'&';
            }
        }
        $stringToBeSigned = substr($stringToBeSigned, 0, -1);
        unset($k, $v);

        return $stringToBeSigned;
    }
}
