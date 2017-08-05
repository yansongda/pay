<?php 

namespace Yansongda\Pay\Gateways;

/**
*   
*/
class AlipayGateway extends Gateway
{
    const WEB_METHOD = 'alipay.trade.page.pay';
    const WEB_PRODUCT_CODE = 'FAST_INSTANT_TRADE_PAY';

    const WAP_METHOD = 'alipay.trade.wap.pay';
    const WAP_PRODUCT_CODE = 'QUICK_WAP_WAY';

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
        'disable_pay_channels' => '',
        'timeout_express' => '15m',
    ];

    /**
     * [__construct description]
     * @author yansongda <me@yansongda.cn>
     * @version 2017-08-05
     * @param   [type]     $config [description]
     */
    public function __construct($config)
    {
        $this->public_config['app_id'] = $config['app_id'];
        $this->public_config['notify'] = $config['notify'];
        $this->public_config['return'] = $config['return'];
    }

    /**
     * 对外接口-支付
     * @author JasonYan <me@yansongda.cn>
     * @version 2017-07-30
     * @param   [type]     $biz_config [description]
     * @param   [type]     $type       [description]
     * @return  [type]                 [description]
     */
    public function pay($biz_config, $type = 'web')
    {

        $this->biz_config = array_merge($this->biz_config, $biz_config);

    }

    /**
     * 对外接口-退款
     * @author JasonYan <me@yansongda.cn>
     * @version 2017-07-29
     * @return  [type]     [description]
     */
    public function refund()
    {

    }

    /**
     * 对外接口-关闭
     * @author JasonYan <me@yansongda.cn>
     * @version 2017-07-29
     * @return  [type]     [description]
     */
    public function close()
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

    protected function sign()
    {
        # code...
    }
}