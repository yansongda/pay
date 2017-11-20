<?php

namespace Yansongda\Pay\Gateways\Wechat;

use Yansongda\Pay\Exceptions\InvalidArgumentException;

class ScanGateway extends Wechat
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
        return 'NATIVE';
    }

    /**
     * pay a order using modelTWO.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param array $config_biz
     *
     * @return string
     */
    public function pay(array $config_biz = [])
    {
        // 服务商模式下，扫码支付 sub_appid 可以为空
        // @link https://pay.weixin.qq.com/wiki/doc/api/native_sl.php?chapter=9_1
        if (is_null($this->user_config->get('app_id')) && is_null($this->user_config->get('service_app_id'))) {
            throw new InvalidArgumentException('Missing Config -- [app_id]');
        }

        return $this->preOrder($config_biz)['code_url'];
    }
}
