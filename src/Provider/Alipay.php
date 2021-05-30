<?php

declare(strict_types=1);

namespace Yansongda\Pay\Provider;

use Psr\Http\Message\RequestInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Yansongda\Pay\Contract\ShortcutInterface;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Alipay\FilterPlugin;
use Yansongda\Pay\Plugin\Alipay\PreparePlugin;
use Yansongda\Pay\Plugin\Alipay\RadarPlugin;
use Yansongda\Pay\Plugin\Alipay\SignPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Pipeline;
use Yansongda\Supports\Str;

class Alipay
{
    public const URL = [
        Pay::MODE_NORMAL => 'https://openapi.alipay.com/gateway.do',
        Pay::MODE_SANDBOX => 'https://openapi.alipaydev.com/gateway.do',
        Pay::MODE_SERVICE => 'https://openapi.alipay.com/gateway.do',
    ];

    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     *
     * @return \Yansongda\Supports\Collection
     */
    public function __call(string $shortcut, array $params)
    {
        $plugin = '\\Yansongda\\Pay\\Plugin\\Alipay\\Shortcut\\'.
            Str::studly($shortcut).'Shortcut';

        if (!class_exists($plugin) || !in_array(ShortcutInterface::class, class_implements($plugin))) {
            throw new InvalidParamsException(InvalidParamsException::SHORTCUT_NOT_FOUND, "[$plugin] not found");
        }

        /* @var ShortcutInterface $money */
        $money = Pay::get($plugin);

        return $this->pay($money->getPlugins(), ...$params);
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     *
     * @return \Yansongda\Supports\Collection|\Symfony\Component\HttpFoundation\Response
     */
    public function pay(array $plugins, array $params)
    {
        $plugins = array_merge(
            [PreparePlugin::class],
            $plugins,
            [FilterPlugin::class, SignPlugin::class, RadarPlugin::class]
        );

        /* @var Pipeline $pipeline */
        $pipeline = Pay::get(Pipeline::class);

        return $pipeline
            ->send((new Rocket())->setParams($params)->setPayload(new Collection()))
            ->through($plugins)
            ->via('assembly')
            ->then(function ($rocket) {
                return $this->ignite($rocket);
            });
    }

    public function ignite(Rocket $rocket)
    {
    }

    protected function launchResponse(RequestInterface $radar, Collection $payload): Response
    {
        $method = $radar->getMethod();
        $endpoint = $radar->getUri()->getScheme().'://'.$radar->getUri()->getHost();

        if ('GET' === $method) {
            return new RedirectResponse($radar->getUri()->__toString());
        }

        $sHtml = "<form id='alipay_submit' name='alipay_submit' action='".$endpoint."' method='".$method."'>";
        foreach ($payload->all() as $key => $val) {
            $val = str_replace("'", '&apos;', $val);
            $sHtml .= "<input type='hidden' name='".$key."' value='".$val."'/>";
        }
        $sHtml .= "<input type='submit' value='ok' style='display:none;'></form>";
        $sHtml .= "<script>document.forms['alipay_submit'].submit();</script>";

        return new Response($sHtml);
    }
}
