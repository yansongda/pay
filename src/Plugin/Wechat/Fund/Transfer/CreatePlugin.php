<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Fund\Transfer;

use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Exception\InvalidResponseException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Wechat\GeneralPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Traits\HasWechatEncryption;
use Yansongda\Supports\Collection;

use function Yansongda\Pay\encrypt_wechat_contents;
use function Yansongda\Pay\get_wechat_config;

/**
 * @see https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter4_3_1.shtml
 */
class CreatePlugin extends GeneralPlugin
{
    use HasWechatEncryption;

    /**
     * @throws ContainerException
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     * @throws InvalidResponseException
     * @throws ServiceNotFoundException
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
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    protected function getWechatId(array $params, Collection $payload): array
    {
        $config = get_wechat_config($params);
        $key = $this->getConfigKey($params);

        $appId = [
            'appid' => $payload->get('appid', $config[$key] ?? ''),
        ];

        if (Pay::MODE_SERVICE === ($config['mode'] ?? null)) {
            $appId = [
                'sub_mchid' => $payload->get('sub_mchid', $config['sub_mch_id'] ?? ''),
            ];
        }

        return $appId;
    }

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
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
