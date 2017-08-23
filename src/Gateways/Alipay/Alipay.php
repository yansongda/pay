<?php

namespace Yansongda\Pay\Gateways\Alipay;

use Yansongda\Pay\Support\Config;
use Yansongda\Pay\Traits\HasHttpRequest;
use Yansongda\Pay\Contracts\GatewayInterface;
use Yansongda\Pay\Exceptions\GatewayException;
use Yansongda\Pay\Exceptions\InvalidArgumentException;

abstract class Alipay implements GatewayInterface
{
    use HasHttpRequest;

    /**
     * 支付宝支付网关.
     *
     * @var string
     */
    protected $gateway = 'https://openapi.alipay.com/gateway.do';

    /**
     * 支付宝公共参数.
     *
     * @var array
     */
    protected $config;

    /**
     * 用户的传参.
     *
     * @var \Yansongda\Pay\Support\Config
     */
    protected $user_config;

    /**
     * [__construct description].
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @version 2017-08-14
     *
     * @param   array      $config [description]
     */
    public function __construct(array $config)
    {
        $this->user_config = new Config($config);

        if (is_null($this->user_config->get('app_id'))) {
            throw new InvalidArgumentException("Missing Config -- [app_id]");
        }

        $this->config = [
            'app_id' => $this->user_config->get('app_id'),
            'method' => '',
            'format' => 'JSON',
            'charset' => 'utf-8',
            'sign_type' => 'RSA2',
            'version' => '1.0',
            'return_url' => $this->user_config->get('return_url', ''),
            'notify_url' => $this->user_config->get('notify_url', ''),
            'timestamp' => date('Y-m-d H:i:s'),
            'sign' => '',
            'biz_content' => '',
        ];
    }


    /**
     * 对外接口 - 支付.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @version 2017-08-15
     *
     * @param   array      $config_biz [description]
     *
     * @return  string                 [description]
     */
    abstract public function pay($config_biz)
    {
        $config_biz['product_code'] = $this->getProductCode();

        $this->config['method'] = $this->getMethod();
        $this->config['biz_content'] = json_encode($config_biz, JSON_UNESCAPED_UNICODE);
        $this->config['sign'] = $this->getSign();
    }

    /**
     * 对外接口 - 退款.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @version 2017-08-15
     *
     * @param   mixed      $config_biz [description]
     *
     * @return  array|boolean          [description]
     */
    public function refund($config_biz, $refund_amount = null)
    {
        if (!is_array($config_biz)) {
            $config_biz = [
                'out_trade_no' => $config_biz,
                'refund_amount' => $refund_amount,
            ];
        }

        return $this->getResult($config_biz, 'alipay.trade.refund');
    }

    /**
     * 对外接口 - 关闭.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @version 2017-08-15
     *
     * @param   array|string $config_biz [description]
     *
     * @return  array|boolean            [description]
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
     * 对外接口 - 订单查询
     * @author yansongda <me@yansongda.cn>
     * 
     * @version 2017-08-19
     * 
     * @param   string     $out_trade_no 商家订单号
     * 
     * @return  array|boolean            [description]
     */
    public function find($out_trade_no = '')
    {
        $config_biz = [
            'out_trade_no' => $out_trade_no,
        ];
        
        return $this->getResult($config_biz, 'alipay.trade.query');
    }

    /**
     * 对外接口 - 验证.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @version 2017-08-11
     *
     * @param   array      $data 待签名数组
     * @param   string     $sign 签名字符串-支付宝服务器发送过来的原始串
     * @param   bool       $sync 是否同步返回验证
     *
     * @return  array|boolean    [description]
     */
    public function verify($data, $sign = null, $sync = false)
    {
        if (is_null($this->user_config->get('ali_public_key'))) {
            throw new InvalidArgumentException("Missing Config -- [ali_public_key]");
        }

        $sign = is_null($sign) ? $data['sign'] : $sign;

        $res = "-----BEGIN PUBLIC KEY-----\n" .
                wordwrap($this->user_config->get('ali_public_key'), 64, "\n", true) .
                "\n-----END PUBLIC KEY-----";

        $toVerify = $sync ? json_encode($data, JSON_UNESCAPED_UNICODE) : $this->getSignContent($data, true);
        
        return openssl_verify($toVerify, base64_decode($sign), $res, OPENSSL_ALGO_SHA256) === 1 ? $data : false;
    }

    /**
     * [getMethod description].
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @version 2017-08-10
     *
     * @return  string     [description]
     */
    abstract protected function getMethod();

    /**
     * [getProductCode description].
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @version 2017-08-10
     *
     * @return  string     [description]
     */
    abstract protected function getProductCode();

    /**
     * [buildHtmlPay description]
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @version 2017-08-11
     *
     * @return  string     [description]
     */
    protected function buildPayHtml()
    {
        $sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='" . $this->gateway . "?charset=utf-8' method='POST'>";
        while (list ($key, $val) = each($this->config)) {
            $val = str_replace("'", "&apos;", $val);
            $sHtml .= "<input type='hidden' name='" . $key . "' value='" . $val . "'/>";
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
     * @version 2017-08-12
     *
     * @param   array      $config_biz [description]
     * @param   string     $method     [description]
     *
     * @return  array|boolean          [description]
     */
    protected function getResult($config_biz, $method)
    {
        $this->config['biz_content'] = json_encode($config_biz, JSON_UNESCAPED_UNICODE);
        $this->config['method'] = $method;
        $this->config['sign'] = $this->getSign();

        $method = str_replace('.', '_', $method) . '_response';
        
        $data = json_decode($this->post($this->gateway, $this->config), true);

        if (!isset($data[$method]['code']) || $data[$method]['code'] !== '10000') {
            throw new GatewayException(
                'get result error:' . $data[$method]['msg'] . ' - ' . $data[$method]['sub_msg'],
                $data[$method]['code'],
                $data);
        }

        return $this->verify($data[$method], $data['sign'], true);
    }

    /**
     * 签名.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @version 2017-08-10
     *
     * @return  string     [description]
     */
    protected function getSign()
    {
        if (is_null($this->user_config->get('private_key'))) {
            throw new InvalidArgumentException("Missing Config -- [private_key]");
        }

        $res = "-----BEGIN RSA PRIVATE KEY-----\n" .
                wordwrap($this->user_config->get('private_key'), 64, "\n", true) .
                "\n-----END RSA PRIVATE KEY-----";

        openssl_sign($this->getSignContent($this->config), $sign, $res, OPENSSL_ALGO_SHA256);

        return base64_encode($sign);
    }

    /**
     * 待签名数组.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @version 2017-08-11
     *
     * @param   array      $toBeSigned [description]
     * @param   boolean    $verify     是否验证签名
     *
     * @return  string                 [description]
     */
    protected function getSignContent(array $toBeSigned, $verify = false)
    {
        ksort($toBeSigned);

        $stringToBeSigned = "";
        foreach ($toBeSigned as $k => $v) {
            if ($verify && $k != 'sign' && $k != 'sign_type') {
                $stringToBeSigned .= $k . "=" . $v . "&";
            }
            if (!$verify && $v !== '' && !is_null($v) && $k != 'sign' && "@" != substr($v, 0, 1)) {
                $stringToBeSigned .= $k . "=" . $v . "&";
            }
        }
        $stringToBeSigned = substr($stringToBeSigned, 0, -1);
        unset ($k, $v);

        return $stringToBeSigned;
    }

}
