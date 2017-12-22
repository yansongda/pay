<?php

/**
 * 发放裂变红包
 * Class GroupredpackGateway
 * Date: 2017/12/21
 * Time: 19:23
 * Com:萌点云科技（深圳）有限公司.
 *
 * Author:陈老司机
 *
 * Email:690712575@qq.com
 */

namespace Yansongda\Pay\Gateways\Wechat;

use Yansongda\Pay\Exceptions\GatewayException;
use Yansongda\Pay\Exceptions\InvalidArgumentException;

class GroupredpackGateway extends Wechat
{
    /**
     * @var string
     */
    protected $gateway_transfer = 'mmpaymkttransfers/sendgroupredpack';

    /**
     * pay a order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param array $config_biz
     *
     * @return mixed
     */
    public function pay(array $config_biz = [])
    {
        if (is_null($this->user_config->get('app_id'))) {
            throw new InvalidArgumentException('Missing Config -- [app_id]');
        }
        unset($this->config['sign_type']);
        unset($this->config['trade_type']);
        unset($this->config['notify_url']);
        unset($this->config['app_id']);
        unset($this->config['appid']);
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
}
