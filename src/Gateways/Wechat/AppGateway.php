<?php

namespace Yansongda\Pay\Gateways\Wechat;

use Yansongda\Pay\Exceptions\InvalidArgumentException;

class AppGateway extends Wechat
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
        return 'APP';
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
        if (is_null($this->user_config->get('appid'))) {
            throw new InvalidArgumentException('Missing Config -- [appid]');
        }

        if (isset($this->config['sub_appid'])) {
            $this->config['sub_appid'] = $this->user_config->get('appid');
        } else {
            $this->config['appid'] = $this->user_config->get('appid');
        }

        $partnerid = $this->user_config->get('mch_id');
        if (isset($this->config['sub_mch_id'])) {
            $partnerid = $this->config['sub_mch_id'];
        }

        $payRequest = [
            'appid'     => $this->user_config->get('appid'), // 服务商模式下此处为子商户的 appid
            'partnerid' => $partnerid, // 服务商模式下此处为子商户的 mch_id
            'prepayid'  => $this->preOrder($config_biz)['prepay_id'],
            'timestamp' => time(),
            'noncestr'  => $this->createNonceStr(),
            'package'   => 'Sign=WXPay',
        ];
        $payRequest['sign'] = $this->getSign($payRequest);

        return $payRequest;
    }
}
