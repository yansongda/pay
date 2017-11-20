<?php

namespace Yansongda\Pay\Gateways\Wechat;

use Yansongda\Pay\Exceptions\InvalidArgumentException;

class WapGateway extends Wechat
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
        return 'MWEB';
    }

    /**
     * pay a order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param array $config_biz
     *
     * @return string
     */
    public function pay(array $config_biz = [])
    {
        // 服务商模式下，网页H5支付 sub_appid 可以为空
        // @link https://pay.weixin.qq.com/wiki/doc/api/H5_sl.php?chapter=9_20&index=1
        if (is_null($this->user_config->get('app_id')) && is_null($this->user_config->get('service_app_id'))) {
            throw new InvalidArgumentException('Missing Config -- [app_id]');
        }

        $data = $this->preOrder($config_biz);

        return is_null($this->user_config->get('return_url')) ? $data['mweb_url'] : $data['mweb_url'].
                        '&redirect_url='.urlencode($this->user_config->get('return_url'));
    }
}
