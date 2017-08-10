<?php 

namespace Yansongda\Pay\Gateways\Alipay;

use Yansongda\Pay\Support\Config;
use Yansongda\Pay\Contracts\GatewayInterface;
use Yansongda\Pay\Exceptions\InvalidArgumentException;

/**
*   
*/
abstract class Alipay implements GatewayInterface
{
    /**
     * 支付宝支付网关
     * @var string
     */
    protected $gateway = 'https://openapi.alipaydev.com/gateway.do';

    /**
     * 支付宝公共参数
     * @var [type]
     */
    protected $config = [
        'app_id' => '',
        'method' => '',
        'format' => 'JSON',
        'charset' => 'utf-8',
        'sign_type' => 'RSA2',
        'version' => '1.0',
        'timestamp' => '',
        'sign' => '',
        'notify_url' => '',
        'return_url' => '',
        'bizContent' => '',
    ];

    /**
     * 业务参数
     * @var [type]
     */
    protected $config_biz = [
        'out_trade_no' => '',
        'product_code' => '',
        'total_amount' => '',
        'subject' => '',
        'timeout_express' => '15m',
    ];

    /**
     * 用户的传参
     * @var [type]
     */
    protected $user_config = [
        'app_id' => '',
        'notify_url' => '',
        'return_url' => '',
        'ali_public_key' => '',
        'private_key' => '',
    ];

    /**
     * [__construct description]
     * @author yansongda <me@yansongda.cn>
     * @version 2017-08-05
     * @param   [type]     $config [description]
     */
    public function __construct($config)
    {
        $this->user_config = new Config(array_merge($this->user_config, $config));

        foreach ($this->user_config->get() as $key => $value) {
            if ($value === '') {
                throw new InvalidArgumentException("Config is incomplete. Missing [$key]");
            }
            if (array_key_exists($key, $this->config)) {
                $this->config[$key] = $value;
            }
        }
    }

    /**
     * [getMethod description]
     * @author yansongda <me@yansongda.cn>
     * @version 2017-08-10
     * @return  [type]     [description]
     */
    abstract protected function getMethod();

    /**
     * [getProductCode description]
     * @author yansongda <me@yansongda.cn>
     * @version 2017-08-10
     * @return  [type]     [description]
     */
    abstract protected function getProductCode();

    /**
     * 获取最后的参数
     * @author yansongda <me@yansongda.cn>
     * @version 2017-08-10
     * @param   [type]     $config_biz [description]
     * @return  [type]                 [description]
     */
    protected function getFinalConfig($config_biz) {
        $this->config_biz = array_merge($this->config_biz, $config_biz);

        foreach ($this->config_biz as $key => $value) {
            if ($value === '' && $key != 'product_code') {
                throw new InvalidArgumentException("BizConfig is invalid. Missing [$key]");
            }
        }

        $this->config_biz['product_code'] = $this->getProductCode();

        $this->config['method'] = $this->getMethod();
        $this->config['timestamp'] = date('Y-m-d H:i:s');
        $this->config['bizContent'] = json_encode($this->config_biz);
        $this->config['sign'] = $this->getSign();
    }

    /**
     * 签名
     * @author yansongda <me@yansongda.cn>
     * @version 2017-08-10
     * @return  [type]     [description]
     */
    protected function getSign()
    {
        $res = "-----BEGIN RSA PRIVATE KEY-----\n" .
                wordwrap($this->user_config->get('private_key'), 64, "\n", true) .
                "\n-----END RSA PRIVATE KEY-----";

        openssl_sign($this->getSignContent(), $sign, $res, OPENSSL_ALGO_SHA256);

        return base64_encode($sign);
    }

    /**
     * 带签名
     * @author yansongda <me@yansongda.cn>
     * @version 2017-08-10
     * @return  [type]     [description]
     */
    protected function getSignContent()
    {
        ksort($this->config);

        $stringToBeSigned = "";
        foreach ($this->config as $k => $v) {
            if ($v !== '' && $k != 'sign' && "@" != substr($v, 0, 1)) {
                $stringToBeSigned .= $k . "=" . $v . "&";
            }
        }
        substr($stringToBeSigned, 0, -1);
        unset ($k, $v);

        return $stringToBeSigned;
    }

    /**
     * 对外接口-支付
     * @author JasonYan <me@yansongda.cn>
     * @version 2017-07-30
     * @param   [type]     $config_biz [description]
     * @param   [type]     $type       [description]
     * @return  [type]                 [description]
     */
    public function pay($config_biz = []) {
        $this->getFinalConfig($config_biz);
    }

    /**
     * 对外接口-退款
     * @author JasonYan <me@yansongda.cn>
     * @version 2017-07-29
     * @return  [type]     [description]
     */
    public function refund() {

    }

    /**
     * 对外接口-关闭
     * @author JasonYan <me@yansongda.cn>
     * @version 2017-07-29
     * @return  [type]     [description]
     */
    public function close() {

    }

}
