<?php

namespace Yansongda\Pay\Gateways\Wechat;

use Yansongda\Pay\Exceptions\InvalidArgumentException;

class MpGateway extends Wechat
{
    /**
     * get trade type config.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @return string
     */
    protected function getTradeType()
    {
        return 'JSAPI';
    }

    /**
     * pay a order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param array $config_biz
     *
     * @return array
     */
    public function pay(array $config_biz = [])
    {
        // 服务商模式下，公众号支付 sub_appid 可以为空，此时微信 H5 调起支付时的 appId 可以是服务商的 appid
        // @link https://pay.weixin.qq.com/wiki/doc/api/jsapi_sl.php?chapter=9_1
        if (is_null($this->user_config->get('app_id')) && is_null($this->user_config->get('service_app_id'))) {
            throw new InvalidArgumentException('Missing Config -- [app_id]');
        }

        $appId = $this->user_config->get('app_id', '');
        if(!$appId) {
            $appId = $this->user_config->get('service_app_id');
        }

        $payRequest = [
            'appId'     => $appId,
            'timeStamp' => time(),
            'nonceStr'  => $this->createNonceStr(),
            'package'   => 'prepay_id='.$this->preOrder($config_biz)['prepay_id'],
            'signType'  => 'MD5',
        ];
        $payRequest['paySign'] = $this->getSign($payRequest);

        return $payRequest;
    }
}
