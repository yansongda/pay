<?php

namespace Yansongda\Pay\Gateways\Wechat;

use Yansongda\Pay\Exceptions\GatewayException;
use Yansongda\Pay\Exceptions\InvalidArgumentException;

class TransferGateway extends Wechat
{
    /**
     * @var string
     */
    protected $gateway_transfer = 'mmpaymkttransfers/promotion/transfers';

    /**
     * get trade type config.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @return string
     */
    protected function getTradeType()
    {
        return '';
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
        if (is_null($this->user_config->get('app_id'))) {
            throw new InvalidArgumentException('Missing Config -- [app_id]');
        }

        $config_biz['mch_appid'] = $this->config['appid'];
        $config_biz['mchid'] = $this->config['mch_id'];

        unset($this->config['appid']);
        unset($this->config['mch_id']);
        unset($this->config['sign_type']);
        unset($this->config['trade_type']);
        unset($this->config['notify_url']);

        $this->config = array_merge($this->config, $config_biz);

        $this->config['sign'] = $this->getSign($this->config);

        $data = $this->fromXml($this->post(
            $this->endpoint.$this->gateway_transfer,
            $this->toXml($this->config),
            [
                'cert'    => $this->user_config->get('cert_client', ''),
                'ssl_key' => $this->user_config->get('cert_key', ''),
            ]
        ));

        if (!isset($data['return_code']) || $data['return_code'] !== 'SUCCESS' || $data['result_code'] !== 'SUCCESS') {
            $error = 'getResult error:'.$data['return_msg'];
            $error .= isset($data['err_code_des']) ? ' - '.$data['err_code_des'] : '';
        }

        if (isset($error)) {
            throw new GatewayException(
                $error,
                20000,
                $data);
        }

        return $data;
    }
}
