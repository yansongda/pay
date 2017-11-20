<?php

namespace Yansongda\Pay\Gateways\Wechat;

use Yansongda\Pay\Exceptions\InvalidArgumentException;

class PosGateway extends Wechat
{
    /**
     * @var string
     */
    protected $gateway = 'https://api.mch.weixin.qq.com/pay/micropay';

    /**
     * get trade type config.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @return string
     */
    protected function getTradeType()
    {
        return 'MICROPAY';
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
        // 服务商模式下，刷卡支付 sub_appid 可以为空
        // @link https://pay.weixin.qq.com/wiki/doc/api/micropay_sl.php?chapter=9_10&index=1
        if (is_null($this->user_config->get('app_id')) && is_null($this->user_config->get('service_app_id'))) {
            throw new InvalidArgumentException('Missing Config -- [app_id]');
        }

        unset($this->config['trade_type']);
        unset($this->config['notify_url']);

        return $this->preOrder($config_biz);
    }
}
