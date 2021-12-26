<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Fund\Transfer;

use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Wechat\GeneralPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Config;

class CreatePlugin extends GeneralPlugin
{
    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     * @throws \Yansongda\Pay\Exception\InvalidResponseException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    protected function doSomething(Rocket $rocket): void
    {
        $params = $rocket->getParams();
        $config = get_wechat_config($params);

        $extra = $this->getWechatId($config);

        if (!empty($params['transfer_detail_list'][0]['user_name'] ?? '')) {
            if (empty($config->get('wechat_public_cert_path'))) {
                reload_wechat_public_certs($params);
            }

            if (empty($params['_serial_no'])) {
                mt_srand();
                $params['_serial_no'] = strval(array_rand($config->get('wechat_public_cert_path')));
                $rocket->setParams($params);
            }

            $extra['transfer_detail_list'] = $this->getEncryptUserName($params);
        }

        $rocket->mergePayload($extra);
    }

    protected function getUri(Rocket $rocket): string
    {
        return 'v3/transfer/batches';
    }

    protected function getPartnerUri(Rocket $rocket): string
    {
        return 'v3/partner-transfer/batches';
    }

    protected function getWechatId(Config $config): array
    {
        $appId = [
            'appid' => $config->get('mp_app_id'),
        ];

        if (Pay::MODE_SERVICE == $config->get('mode')) {
            $appId = [
                'sub_mchid' => $config->get('sub_mch_id', ''),
            ];
        }

        return $appId;
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    protected function getEncryptUserName(array $params): array
    {
        $serialNo = $params['_serial_no'] ?? '';
        $lists = $params['transfer_detail_list'] ?? [];

        foreach ($lists as $key => $list) {
            $lists[$key]['user_name'] = encrypt_wechat_contents($params, $list['user_name'], $serialNo);
        }

        return $lists;
    }
}
