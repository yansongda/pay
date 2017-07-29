<?php 

namespace Yansongda\Pay\Gateways\Alipay;

/**
* 
*/
class WayGateway extends Gateway
{
    
    const METHOD = 'alipay.trade.wap.pay';

    const PRODUCT_CODE = 'QUICK_WAP_WAY';

    /**
     * 业务参数
     * @var [type]
     */
    private $bizContent = [
        'out_trade_no' => '',
        'product_code' => self::PRODUCT_CODE,
        'total_amount' => '0.01',
        'subject' => 'test production subject',
    ];
    
    /**
     * 支付
     * @author JasonYan <me@yansongda.cn>
     * @version 2017-07-29
     * @param   array      $bizContent 业务参数：out_trade_no ， total_amount 和 subject 必填
     * @return  [type]                 [description]
     */
    public function pay(array $bizContent)
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