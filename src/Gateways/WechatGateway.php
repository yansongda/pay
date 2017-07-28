<?php 

namespace Yansongda\Pay\Gateways;

/**
*   
*/
class WechatGateway extends Gateway
{

    
    /**
     * 支付
     * @author JasonYan <me@yansongda.cn>
     * @version 2017-07-28
     * @return  [type]     [description]
     */
    public function pay()
    {
        
    }

    /**
     * 查看是否支付宝官方发出
     * @author JasonYan <me@yansongda.cn>
     * @version 2017-07-28
     * @return  [type]     [description]
     */
    public function verify()
    {
        # code...
    }

    /**
     * 获取业务参数
     * @author JasonYan <me@yansongda.cn>
     * @version 2017-07-28
     * @return  [type]     [description]
     */
    private function getBizContent()
    {
        # code...
    }

    /**
     * 签名
     * @author JasonYan <me@yansongda.cn>
     * @version 2017-07-28
     * @return  [type]     [description]
     */
    private function sign()
    {
        # code...
    }
}