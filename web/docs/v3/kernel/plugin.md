# 🔌Plugin

得益于 pipeline，Pay 中的所有数据变换都通过 plugin 来实现，
同时 Pay 中也内置了很多常用的 Plugin，因此使用方式非常灵活简单。

其实大家经常使用的 「网站支付」「小程序支付」「查询订单」 等均属于自定义插件，只不过这类插件已经内置在 yansongda/pay 中了，不需要您额外开发即可使用。

## 定义

```php
<?php

declare(strict_types=1);

namespace Yansongda\Pay\Contract;

use Closure;
use Yansongda\Pay\Rocket;

interface PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket;
}
```

## 详细说明

### 支付宝电脑支付

以支付宝的电脑支付为例，我们知道，支付宝电脑支付首先需要 组装(assembly) 一系列支付宝要求的参数，
然后，需要以 form 表单，或者 GET 的方式请求支付宝的地址，这样才能跳转到支付宝的电脑支付页面进行支付。

所以，除了支付宝公共的，生成签名、验签、调用支付宝API 等等公共的事情以外，我们还需要两个 Plugin

- 组装参数 Plugin

```php
<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Trade;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Parser\ResponseParser;
use Yansongda\Pay\Rocket;

class PagePayPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::info('[alipay][PagePayPlugin] 插件开始装载', ['rocket' => $rocket]);

        $rocket->setDirection(ResponseParser::class)
            ->mergePayload([
                'method' => 'alipay.trade.page.pay',
                'biz_content' => array_merge(
                    ['product_code' => 'FAST_INSTANT_TRADE_PAY'],
                    $rocket->getParams()
                ),
            ]);

        Logger::info('[alipay][PagePayPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }
}
```

这个 Plugin 的目的就是为了组装一系列支付宝所需要的参数，同时，由于电脑支付是不需要后端 http 调用支付宝接口的，
只需要一个浏览器的响应，所以，我们把 🚀 的 `Direction` 设置成了 `ResponseParser::class`。

- 跳转响应 Plugin

```php
<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay;

use Closure;
use GuzzleHttp\Psr7\Response;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Arr;
use Yansongda\Supports\Collection;

class HtmlResponsePlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::info('[alipay][HtmlResponsePlugin] 插件开始装载', ['rocket' => $rocket]);

        /* @var Rocket $rocket */
        $rocket = $next($rocket);

        $radar = $rocket->getRadar();

        $response = 'GET' === $radar->getMethod() ?
            $this->buildRedirect($radar->getUri()->__toString(), $rocket->getPayload()) :
            $this->buildHtml($radar->getUri()->__toString(), $rocket->getPayload());

        $rocket->setDestination($response);

        Logger::info('[alipay][HtmlResponsePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
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
```

在处理好支付宝所需要的参数之后，按照其它正常逻辑，应该调用支付宝API获取数据了，
但是由于 电脑支付 不是直接调用支付宝API的，
所以，这里使用了 `后置 plugin` 处理组装相关 html 代码进行 post 或者 GET 请求访问支付宝电脑支付页面。

最后，得益于 🚀 的 `Direction` 机制，最终返回给你的就是一个符合 PSR7 规范的 `Response` 对象了，
您可以集成到任何符合相关规范的框架中。

### 支付宝查询订单

```php
<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay\Trade;

use Yansongda\Pay\Plugin\Alipay\GeneralPlugin;

class QueryPlugin extends GeneralPlugin
{
    protected function getMethod(): string
    {
        return 'alipay.trade.query';
    }
}
```

```php
<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Alipay;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

abstract class GeneralPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::info('[alipay][GeneralPlugin] 通用插件开始装载', ['rocket' => $rocket]);

        $rocket->mergePayload([
            'method' => $this->getMethod(),
            'biz_content' => $rocket->getParams(),
        ]);

        Logger::info('[alipay][GeneralPlugin] 通用插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    abstract protected function getMethod(): string;
}
```

通过以上代码，我们大概能明白，查询订单的 `QueryPlugin` 插件，继承了 `GeneralPlugin` 这个常用插件，
通过支付宝官方文档，我们知道，查询订单的 API 将传参中的 method 改为了 `alipay.trade.query`，其它参数均是个性化参数，和入参有关，
因此，我们在做查询订单时，是需要简单的把 method 按要求更改即可，是不是很简单？

### 微信查询订单

```php
<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat\Pay\Common;

use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Wechat\GeneralPlugin;
use Yansongda\Pay\Rocket;

class QueryPlugin extends GeneralPlugin
{
    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     * @throws \Yansongda\Pay\Exception\InvalidParamsException
     */
    protected function getUri(Rocket $rocket): string
    {
        $config = get_wechat_config($rocket->getParams());
        $payload = $rocket->getPayload();

        if (is_null($payload->get('transaction_id'))) {
            throw new InvalidParamsException(InvalidParamsException::MISSING_NECESSARY_PARAMS);
        }

        return 'v3/pay/transactions/id/'.
            $payload->get('transaction_id').
            '?mchid='.$config->get('mch_id', '');
    }

    protected function getMethod(): string
    {
        return 'GET';
    }

    protected function doSomething(Rocket $rocket): void
    {
        $rocket->setPayload(null);
    }
}

```

```php
<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Wechat;

use Closure;
use Psr\Http\Message\RequestInterface;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Provider\Wechat;
use Yansongda\Pay\Request;
use Yansongda\Pay\Rocket;

abstract class GeneralPlugin implements PluginInterface
{
    /**
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::info('[wechat][GeneralPlugin] 通用插件开始装载', ['rocket' => $rocket]);

        $rocket->setRadar($this->getRequest($rocket));
        $this->doSomething($rocket);

        Logger::info('[wechat][GeneralPlugin] 通用插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    protected function getRequest(Rocket $rocket): RequestInterface
    {
        return new Request(
            $this->getMethod(),
            $this->getUrl($rocket),
            $this->getHeaders(),
        );
    }

    protected function getMethod(): string
    {
        return 'POST';
    }

    /**
     * @throws \Yansongda\Pay\Exception\ContainerDependencyException
     * @throws \Yansongda\Pay\Exception\ContainerException
     * @throws \Yansongda\Pay\Exception\ServiceNotFoundException
     */
    protected function getUrl(Rocket $rocket): string
    {
        $config = get_wechat_config($rocket->getParams());

        return Wechat::URL[$config->get('mode', Pay::MODE_NORMAL)].
            $this->getUri($rocket);
    }

    protected function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
        ];
    }

    abstract protected function doSomething(Rocket $rocket): void;

    abstract protected function getUri(Rocket $rocket): string;
}
```

支付宝和微信的 `QueryPlugin` 和 `GeneralPlugin` 有些许不一样，不过都是为了高度抽象出支付运营商的API。

通过微信官方文档，我们知道，查询订单的 API 将传参中的 url 是随参数变化而变化的，因此我们抽象出了 `getUri` 等方法，方便做各种请求上的调整。


## 通用插件

Pay 内部已经集成了很多通用插件，如 加密，签名，调用支付宝/微信接口等。

只需要简单的使用以下代码即可获取通用插件

```php
$allPlugins = Pay::alipay()->mergeCommonPlugins([QueryPlugin::class]);
```

## 最终调用

在拿到所有的插件之后，就可以愉快的进行调用获取最后的数据了。

```php
$result = Pay::alipay()->pay($allPlugins, $params);
```

代码中的 `$params` 为调用 API 所需要的其它参数。
