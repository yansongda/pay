# 🚀Rocket

`yansongda/artful` 将 `输入参数`、`请求`、`原始响应`、`最终响应` 等抽象成了一种类型 `Rocket`，
在 Pay 项目中，所有的数据扭转都是通过 `Rocket` 来实现的。

`Rocket` 也是由不同的属性组成的，现一一道来

## 📡 Radar

 🚀 的 `雷达`，负责导航使用。

该属性，实际上最终为一个 `\Psr\Http\Message\RequestInterface` 对象。

所有需要请求 API 的方法，最终都会使用这个 📡 ，去调用 http 接口请求 API。

## 🛠 Params

 🚀 的 `输入参数`，该属性为 `array`，实际存储的就是输入的所有参数，不做任何改变。

例如：

```php
[
    'out_trade_no' => '1514027114',
    'out_request_no' => '1514027114',
    '_action' => 'refund',
];
```

Pay 项目中，将所有以 _下划线_ 开始的参数都定义为 `特殊参数`，在最终请求时，**一定不会带入请求体中**。

## ⚙️ Payload

🚀 的 `有效载荷`，该属性类型为 `\Yansongda\Supports\Collection`，
实际存储的是需要请求 API 的所有有用参数。

:::warning
注意，payload 和 params 是不一样的，params 存储的是原封不动的输入参数，payload 是经过一系列插件 "处理" 后的 有效载荷。
:::

## 📨 Packer

🚀 的 `打包器`。

实际作用为：将 `Payload` 打包成 `Radar` 中请求体的最终格式，同时，可能将接受到的响应解析成具体的格式。具体来说可能就是 `json`, `xml` 等。

## 🗝 Direction

🚀 的 `方向`。

实际的作用为：把控最终请求需要解包的类型，它将接收 `Packer`。

例如，支付宝电脑支付中，其最终返回的是一个 `Response` 对象，不需要直接后端 http 请求支付宝接口的，
所以当使用支付宝电脑支付时，其 Direction 为 `Yansongda\Artful\Direction\ResponseDirection::class`。

绝大多数情况下，均默认为：`Yansongda\Artful\Direction\CollectionParser::class`

## 🌟 ️Destination

🚀 的 `目的地`。

实际作用为：最终返回的类

`Destination` 和 `Direction` 密切相关。 `Direction` 直接决定这 `Destination` 的值和类型。

- 当 `Direction` 为 `CollectionParser` 时

    `Destination` 最终返回的是 `Collection` 对象

- 当 `Direction` 为 `ResponseDirection` 时

    `Destination` 最终返回的是 Response 对象

- 当 `Direction` 为 `NoHttpRequestDirection` 时

    `Destination` 最终返回的是 原样的 `Radar`

- 当 `Direction` 为 `OriginResponseDirection` 时

  `Destination` 最终返回的是 `Rocket` 中的 `DestinationOrigin`

## ✨ DestinationOrigin

🚀 的 `原始目的地`。

实际作用为：当请求 http 接口后的原始 `Response`
