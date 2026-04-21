<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\Pay\Refund;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidConfigException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\WechatConfig;
use Yansongda\Pay\Exception\DecryptException;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Traits\WechatTrait;
use Yansongda\Supports\Collection;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/refund/refunds/create-abnormal-refund.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/refund/refunds/create-abnormal-refund.html
 */
class RefundAbnormalPlugin implements PluginInterface
{
    use WechatTrait;

    /**
     * @throws ContainerException
     * @throws DecryptException
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][V3][Pay][Refund][RefundAbnormalPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $payload = $rocket->getPayload();
        $config = self::getProviderConfig('wechat', $params);
        $refundId = $payload?->get('refund_id') ?? null;

        if (empty($refundId)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 发起异常退款，参数缺少 `refund_id`');
        }

        if (Pay::MODE_SERVICE === $config->getMode()) {
            $data = $this->service($params, $config, $payload);
        }

        $rocket->mergePayload(array_merge(
            [
                '_method' => 'POST',
                '_url' => 'v3/refund/domestic/refunds/'.$refundId.'/apply-abnormal-refund',
                '_service_url' => 'v3/refund/domestic/refunds/'.$refundId.'/apply-abnormal-refund',
            ],
            $data ?? $this->normal($params, $config, $payload)
        ))->exceptPayload('refund_id');

        Logger::info('[Wechat][V3][Pay][Refund][RefundAbnormalPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     * @throws DecryptException
     * @throws InvalidConfigException
     */
    protected function normal(array $params, WechatConfig $config, Collection $payload): array
    {
        return $this->encryptSensitiveData($params, $config, $payload);
    }

    /**
     * @throws ContainerException
     * @throws DecryptException
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    protected function service(array $params, WechatConfig $config, Collection $payload): array
    {
        $data = [
            'sub_mchid' => $payload->get('sub_mchid', $config->getSubMchId() ?? ''),
        ];

        return array_merge($data, $this->encryptSensitiveData($params, $config, $payload));
    }

    /**
     * @throws ContainerException
     * @throws DecryptException
     * @throws InvalidConfigException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    protected function encryptSensitiveData(array $params, WechatConfig $config, Collection $payload): array
    {
        if ($payload->has('bank_account') && $payload->has('real_name')) {
            $data['_serial_no'] = self::getWechatSerialNo($params);

            $config = self::getProviderConfig('wechat', $params);
            $publicKey = self::getWechatPublicKey($config, $data['_serial_no']);

            $data['real_name'] = self::encryptWechatContents($payload->get('real_name'), $publicKey);
            $data['bank_account'] = self::encryptWechatContents($payload->get('bank_account'), $publicKey);
        }

        return $data ?? [];
    }
}
