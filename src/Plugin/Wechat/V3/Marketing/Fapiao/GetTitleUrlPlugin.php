<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\V3\Marketing\Fapiao;

use Closure;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\ServiceNotFoundException;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Supports\Collection;

use function Yansongda\Artful\filter_params;
use function Yansongda\Pay\get_wechat_config;
use function Yansongda\Pay\get_wechat_type_key;

/**
 * @see https://pay.weixin.qq.com/docs/merchant/apis/fapiao/user-title/acquire-fapiao-title-url.html
 */
class GetTitleUrlPlugin implements PluginInterface
{
    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[Wechat][V3][Marketing][Fapiao][GetTitleUrlPlugin] 插件开始装载', ['rocket' => $rocket]);

        $params = $rocket->getParams();
        $payload = $rocket->getPayload();

        if (empty($payload)) {
            throw new InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, '参数异常: 获取抬头填写链接，缺少必要参数');
        }

        $rocket->setPayload([
            '_method' => 'GET',
            '_url' => 'v3/new-tax-control-fapiao/user-title/title-url?'.$this->getQuery($payload, $params)->query(),
        ]);

        Logger::info('[Wechat][V3][Marketing][Fapiao][GetTitleUrlPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    protected function getQuery(Collection $payload, array $params): Collection
    {
        $config = get_wechat_config($params);

        return filter_params($payload)->merge([
            'appid' => $payload->get('appid', $config[get_wechat_type_key($params)] ?? ''),
        ]);
    }
}
