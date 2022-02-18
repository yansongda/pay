<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Fund\Profitsharing;

use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Wechat\GeneralPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Traits\HasWechatEncryption;
use Yansongda\Supports\Collection;

class AddReceiverPlugin extends GeneralPlugin
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
        $config = get_wechat_config($rocket->getParams());
        $extra = $this->getWechatId($config, $rocket->getPayload());

        if (!empty($params['name'] ?? '')) {
            $params = $this->loadSerialNo($params);

            $name = $this->getEncryptUserName($params);
            $params['name'] = $name;
            $extra['name'] = $name;
            $rocket->setParams($params);
        }

        $rocket->mergePayload($extra);
    }

    protected function getUri(Rocket $rocket): string
    {
        return 'v3/profitsharing/receivers/add';
    }

    protected function getWechatId(Collection $config, Collection $payload): array
    {
        $wechatId = [
            'appid' => $config->get('mp_app_id'),
        ];

        if (Pay::MODE_SERVICE == $config->get('mode')) {
            $wechatId['sub_mchid'] = $payload->get('sub_mchid', $config->get('sub_mch_id', ''));
        }

        return $wechatId;
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    protected function getEncryptUserName(array $params): string
    {
        $name = $params['name'] ?? '';
        $publicKey = $this->getPublicKey($params, $params['_serial_no'] ?? '');

        $name = encrypt_wechat_contents($name, $publicKey);

        return $name;
    }
}
