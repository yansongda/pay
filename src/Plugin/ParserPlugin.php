<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\InvalidConfigException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

use function Yansongda\Pay\get_direction;
use function Yansongda\Pay\get_packer;

class ParserPlugin implements PluginInterface
{
    /**
     * @throws InvalidConfigException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        /* @var Rocket $rocket */
        $rocket = $next($rocket);

        Logger::debug('[ParserPlugin] 插件开始装载', ['rocket' => $rocket]);

        /* @var ResponseInterface $response */
        $response = $rocket->getDestination();

        $rocket->setDestination(get_direction($rocket->getDirection())->guide(
            get_packer($rocket->getPacker()),
            $response
        ));

        Logger::debug('[ParserPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
    }
}
