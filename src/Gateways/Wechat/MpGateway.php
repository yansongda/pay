<?php

namespace Yansongda\Pay\Gateways\Wechat;

/**
 * 微信 - �
 * �众号支付.
 */
class MpGateway extends Wechat
{
    /**
     * 交易类型.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @version 2017-08-15
     *
     * @return [type] [description]
     */
    protected function getTradeType()
    {
        return 'JSAPI';
    }

    /**
     * 对外支付.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @version 2017-08-15
     *
     * @param array $config_biz [description]
     *
     * @return [type] [description]
     */
    public function pay(array $config_biz = [])
    {
        $this->config = array_merge($this->config, $config_biz);

        $payRequest = [
            'appId'     => $this->user_config->get('app_id'),
            'timeStamp' => time(),
            'nonceStr'  => $this->createNonceStr(),
            'package'   => 'prepay_id='.$this->preOrder()['prepay_id'],
            'signType'  => 'MD5',
            //"paySign" ： "70EA570631E4BB79628FBCA90534C63FF7FADD89" //微信签名
        ];
        $payRequest['paySign'] = $this->getSign($payRequest);

        return $payRequest;
    }
}
