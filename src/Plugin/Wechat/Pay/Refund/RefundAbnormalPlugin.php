<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\Refund;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\DecryptException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidConfigException;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;

use function Yansongda\Pay\encrypt_wechat_contents;
use function Yansongda\Pay\get_wechat_config;
use function Yansongda\Pay\get_wechat_public_key;
use function Yansongda\Pay\get_wechat_serial_no;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/refund/refunds/create-abnormal-refund.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/refund/refunds/create-abnormal-refund.html
 */
class RefundAbnormalPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws DecryptException
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Pay][Refund][RefundAbnormalPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $payload = $rocket->getPayload();
        $config = get_wechat_config($params);

        if (empty($payload->get('refund_id'))) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 发起异常退款，参数缺少 `refund_id`');
        }

        if (Pay::MODE_SERVICE === $config['mode']) {
            $data = $this->service($params, $config, $payload);
        }

        $rocket->mergePayload(array_merge(
            [
                '_method' => 'POST',
                '_url' => 'v3/refund/domestic/refunds/'.$payload->get('refund_id').'/apply-abnormal-refund',
                '_service_url' => 'v3/refund/domestic/refunds/'.$payload->get('refund_id').'/apply-abnormal-refund',
            ],
            $data ?? $this->normal($params, $config, $payload)
        ));

        Logger::info('[Wechat][Pay][Refund][RefundAbnormalPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     * @throws DecryptException
     * @throws InvalidConfigException
     */
    protected function normal(array $params, array $config, ?Collection $payload): array
    {
        if (is_null($payload)) {
            return [];
        }

        return $this->encryptSensitiveData($params, $config, $payload);
    }

    /**
     * @throws ContainerException
     * @throws DecryptException
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    protected function service(array $params, array $config, ?Collection $payload): array
    {
        $data = [
            'sub_mchid' => $config['sub_mch_id'] ?? '',
        ];

        if (is_null($payload)) {
            return $data;
        }

        return array_merge($data, $this->encryptSensitiveData($params, $config, $payload));
    }

    /**
     * @throws ContainerException
     * @throws DecryptException
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    protected function encryptSensitiveData(array $params, array $config, ?Collection $payload): array
    {
        if ($payload->has('bank_account') && $payload->has('real_name')) {
            $data['_serial_no'] = get_wechat_serial_no($params);
            $publicKey = get_wechat_public_key($config, $data['_serial_no']);

            $data['real_name'] = encrypt_wechat_contents($payload->get('real_name'), $publicKey);
            $data['bank_account'] = encrypt_wechat_contents($payload->get('bank_account'), $publicKey);
        }

        return $data ?? [];
    }
}
