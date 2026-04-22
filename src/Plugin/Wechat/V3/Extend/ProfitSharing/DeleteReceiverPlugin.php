<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\Extend\ProfitSharing;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Config\WechatConfig;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Traits\WechatTrait;
use Yansongda\Supports\Collection;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/profit-sharing/receivers/delete-receiver.html
 * @see https://pay.weixin.qq.com/docs/partner/apis/profit-sharing/receivers/delete-receiver.html
 */
class DeleteReceiverPlugin implements PluginInterface
{
    use WechatTrait;

    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][Extend][ProfitSharing][DeleteReceiverPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();

        /** @var WechatConfig $config */
        $config = self::getProviderConfig('wechat', $params);
        $payload = $rocket->getPayload();

        if (is_null($payload)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 缺少分账参数');
        }

        if (Pay::MODE_SERVICE === $config->getMode()) {
            $data = $this->service($payload, $params, $config);
        }

        $rocket->mergePayload(array_merge(
            [
                '_method' => 'POST',
                '_url' => 'v3/profitsharing/receivers/delete',
                '_service_url' => 'v3/profitsharing/receivers/delete',
            ],
            $data ?? $this->normal($params, $config),
        ));

        Logger::info('[Wechat][Extend][ProfitSharing][DeleteReceiverPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function normal(array $params, WechatConfig $config): array
    {
        return [
            'appid' => $config->getAppIdByType($params['_type'] ?? 'mp') ?? '',
        ];
    }

    protected function service(Collection $payload, array $params, WechatConfig $config): array
    {
        $data = [
            'sub_mchid' => $payload->get('sub_mchid', $config->getSubMchId() ?? ''),
            'appid' => $config->getAppIdByType($params['_type'] ?? 'mp') ?? '',
        ];

        if ('PERSONAL_SUB_OPENID' === $payload->get('type')) {
            $data['sub_appid'] = $config->getSubAppIdByType($params['_type'] ?? 'mp') ?? '';
        }

        return $data;
    }
}
