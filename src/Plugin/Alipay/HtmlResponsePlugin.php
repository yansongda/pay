<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay;

use Closure;
use GuzzleHttp\Psr7\Response;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Arr;
use Yansongda\Supports\Collection;

class HtmlResponsePlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        $rocket->setDestination(new Response());

        /* @var Rocket $rocket */
        $rocket = $next($rocket);

        $radar = $rocket->getRadar();

        $response = 'GET' === $radar->getMethod() ?
            $this->buildRedirect($radar->getUri()->__toString(), $rocket->getPayload()) :
            $this->buildHtml($radar->getUri()->__toString(), $rocket->getPayload());

        return $rocket->setDestination($response);
    }

    protected function buildRedirect(string $endpoint, Collection $payload): Response
    {
        $url = $endpoint.'?'.Arr::query($payload->all());

        $content = sprintf('<!DOCTYPE html>
                    <html lang="en">
                        <head>
                            <meta charset="UTF-8" />
                            <meta http-equiv="refresh" content="0;url=\'%1$s\'" />
                    
                            <title>Redirecting to %1$s</title>
                        </head>
                        <body>
                            Redirecting to %1$s.
                        </body>
                    </html>', htmlspecialchars($url, ENT_QUOTES)
        );

        return new Response(302, ['Location' => $url], $content);
    }

    protected function buildHtml(string $endpoint, Collection $payload): Response
    {
        $sHtml = "<form id='alipay_submit' name='alipay_submit' action='".$endpoint."' method='POST'>";
        foreach ($payload->all() as $key => $val) {
            $val = str_replace("'", '&apos;', $val);
            $sHtml .= "<input type='hidden' name='".$key."' value='".$val."'/>";
        }
        $sHtml .= "<input type='submit' value='ok' style='display:none;'></form>";
        $sHtml .= "<script>document.forms['alipay_submit'].submit();</script>";

        return new Response(200, [], $sHtml);
    }
}
