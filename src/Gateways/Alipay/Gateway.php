<?php 
namespace Yansongda\Pay\Gateways\Alipay;

use Yansongda\Pay\Support\Config;
use Yansongda\Pay\Contracts\GatewayInterface;

/**
*   
*/
abstract class Gateway implements GatewayInterface
{
    /**
     * [$config description]
     * @var [type]
     */
    protected $public_config = [
        'app_id' => '',
        'method' => '',
        'format' => 'JSON',
        'charset' => 'utf-8',
        'sign_type' => 'RSA2',
        'version' => '1.0',
        'timestamp' => date('Y-m-d H:i:s'),
        'sign' => '',
        'notify' => '',
        'return' => '',
        'bizContent' => '',
    ];

    /**
     * 业务参数
     * @var [type]
     */
    protected $biz_config = [
        'out_trade_no' => '',
        'product_code' => '',
        'total_amount' => '',
        'subject' => '',
        'timeout_express' => '15m',
    ];
    
    /**
     * [__construct description]
     * @author JasonYan <me@yansongda.cn>
     * @version 2017-07-28
     * @param   array      $config [description]
     */
    public function __construct(array $config = [])
    {
        $this->config = new Config(array_merge($this->config, $config));
    }

    /**
     * 获取业务参数
     * @author JasonYan <me@yansongda.cn>
     * @version 2017-07-29
     * @return  [type]     [description]
     */
    protected function getBizContent()
    {
        # code...
    }

    protected function getSign()
    {
        # code...
    }
}