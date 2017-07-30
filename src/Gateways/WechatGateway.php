<?php 
namespace Yansongda\Pay\Gateways;

use Yansongda\Pay\Support\Config;

/**
*   
*/
class WechatGateway extends Gateway
{
    /**
     * [$config description]
     * @var [type]
     */
    protected $public_config = [
        'appid' => '',
        'mch_id' => '',
        'nonce_str' => '',
        'sign' => '',
        'sign_type' => 'MD5',
        'notify_url' => '',
    ];

    /**
     * 业务参数
     * @var [type]
     */
    protected $biz_config = [
        'out_trade_no' => '',
        'trade_type' => '',
        'total_fee' => '',
        'body' => '',
        'spbill_create_ip' => '',
        'device_info' => '',
    ];

    /**
     * 对外支付
     * @author JasonYan <me@yansongda.cn>
     * @version 2017-07-30
     * @param   [type]     $biz_config [description]
     * @param   [type]     $type       [description]
     * @return  [type]                 [description]
     */
    public function pay($biz_config, $type)
    {
        
    }

    /**
     * 获取业务参数
     * @author JasonYan <me@yansongda.cn>
     * @version 2017-07-29
     * @return  [type]     [description]
     */
    protected function getBizContent()
    {
        return json_encode($this->biz_config);
    }

    protected function getSign()
    {
        # code...
    }
}