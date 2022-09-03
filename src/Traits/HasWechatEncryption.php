<?php

declare(strict_types=1);

namespace Yansongda\Pay\Traits;

use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;

use function Yansongda\Pay\get_wechat_config;
use function Yansongda\Pay\reload_wechat_public_certs;

trait HasWechatEncryption
{
    /**
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     * @throws \Yansongda\Pay\Exception\InvalidResponseException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    public function loadSerialNo(array $params): array
    {
        $config = get_wechat_config($params);

        if (empty($config['wechat_public_cert_path'])) {
            reload_wechat_public_certs($params);

            $config = get_wechat_config($params);
        }

        if (empty($params['_serial_no'])) {
            mt_srand();
            $params['_serial_no'] = strval(array_rand($config['wechat_public_cert_path']));
        }

        return $params;
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    public function getPublicKey(array $params, string $serialNo): string
    {
        $config = get_wechat_config($params);

        $publicKey = $config['wechat_public_cert_path'][$serialNo] ?? null;

        if (empty($publicKey)) {
            throw new InvalidParamsException(Exception::WECHAT_SERIAL_NO_NOT_FOUND, 'Wechat serial no not found: '.$serialNo);
        }

        return $publicKey;
    }
}
