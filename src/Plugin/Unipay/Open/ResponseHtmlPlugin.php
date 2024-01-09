<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Unipay\Open;

use Closure;
use GuzzleHttp\Psr7\Response;
use Yansongda\Artful\Contract\PluginInterface;
use Yansongda\Artful\Logger;
use Yansongda\Artful\Rocket;
use Yansongda\Supports\Collection;

use function Yansongda\Artful\filter_params;

class ResponseHtmlPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        /* @var Rocket $rocket */
        $rocket = $next($rocket);

        Logger::debug('[Unipay][ResponseHtmlPlugin] 插件开始装载', ['rocket' => $rocket]);

        $radar = $rocket->getRadar();
        $payload = $rocket->getPayload();

        $response = $this->buildHtml($radar->getUri()->__toString(), filter_params($payload));

        $rocket->setDestination($response);

        Logger::info('[Unipay][ResponseHtmlPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
    }

    protected function buildHtml(string $endpoint, Collection $payload): Response
    {
        $sHtml = "<form id='pay_form' name='pay_form' action='".$endpoint."' method='POST'>";
        foreach ($payload->all() as $key => $val) {
            $sHtml .= "<input type='hidden' name='".$key."' value='".$val."'/>";
        }
        $sHtml .= "<input type='submit' value='ok' style='display:none;'></form>";
        $sHtml .= "<script>document.forms['pay_form'].submit();</script>";

        return new Response(200, [], $sHtml);
    }
}
