<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Fund\Transfer;

use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Wechat\GeneralPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Traits\HasWechatEncryption;
use Yansongda\Supports\Collection;

class CreatePlugin extends GeneralPlugin
{
    use HasWechatEncryption;

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
        $extra = $this->getWechatId($params, $rocket->getPayload());

        if (!empty($params['transfer_detail_list'][0]['user_name'] ?? '')) {
            $params = $this->loadSerialNo($params);

            $rocket->setParams($params);

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

    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    protected function getWechatId(array $params, Collection $payload): array
    {
        $config = get_wechat_config($params);
        $key = ($params['_type'] ?? 'mp').'_app_id';

        if ('app_app_id' === $key) {
            $key = 'app_id';
        }

        $appId = [
            'appid' => $payload->get('appid', $config->get($key, '')),
        ];

        if (Pay::MODE_SERVICE == $config->get('mode')) {
            $appId = [
                'sub_mchid' => $payload->get('sub_mchid', $config->get('sub_mch_id', '')),
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
        $lists = $params['transfer_detail_list'] ?? [];
        $publicKey = $this->getPublicKey($params, $params['_serial_no'] ?? '');

        foreach ($lists as $key => $list) {
            $lists[$key]['user_name'] = encrypt_wechat_contents($list['user_name'], $publicKey);
        }

        return $lists;
    }
}
