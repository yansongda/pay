<?php 

namespace Yansongda\Pay;

use Yansongda\Pay\Support\Config;

/**
* 
*/
class Pay
{
    /**
     * [$config description]
     * @var [type]
     */
    private $config;

    /**
     * [__construct description]
     * @author JasonYan <me@yansongda.cn>
     * @version 2017-07-29
     * @param   array      $config [description]
     */
    public function __construct(array $config = [])
    {
        $this->config = new Config($config);
    }

    /**
     * 支付
     * @author JasonYan <me@yansongda.cn>
     * @version 2017-07-29
     * @param   string     $type       支持支付宝、微信。格式为'alipay.web','alipay.wap' 和 'wechat.mp','wechat.h5','wechat.scan','wechat.pos','wechat.app'
     * @param   [type]     $biz_config 业务参数:total_money,out_trade_no,subject
     * @return  [type]                 [description]
     */
    public function pay($type, $biz_config)
    {
        # code...
    }

    public function verify($type, $biz_config)
    {
        # code...
    }

    public function query()
    {
        # code...
    }

    public function refund()
    {
        # code...
    }

    public function close()
    {
        # code...
    }
}