# 接收支付宝回调

|   方法名    |               参数               |              返回值              |
|:--------:|:------------------------------:|:------------------------------:|
| callback | 无/array/ServerRequestInterface | Collection \| ResponseInterface |

默认返回 `Yansongda\Supports\Collection`；使用应用网关验证（`'_action' => 'gw'`）时返回 `Psr\Http\Message\ResponseInterface`，详见 [应用网关验证（ISV）](#应用网关验证-isv)。

使用的加密方式为支付宝官方推荐的 **RSA2**，目前只支持这一种加密方式，且没有支持其他加密方式的计划。

## 支付回调

### 例子

```php
Pay::config($this->config);

// 是的，你没有看错，就是这么简单！
$result = Pay::alipay()->callback();
```

### 参数

#### 第一个参数

##### `null`

如果您没有传参，或传 `null` 则 `yansongda/pay` 会自动识别支付宝的回调请求并处理，通过 `Collection` 实例返回支付宝的处理参数

:::warning
建议仅在 php-fpm 下使用，swoole 方式请使用 `ServerRequestInterface` 参数传递方式
:::

##### `ServerRequestInterface`

推荐在 swoole 环境下传递此参数，传递此参数后， yansongda/pay 会自动进行后续处理

##### `array`

也可以自行解析请求参数，传递一个 array 会自动进行后续处理

#### 第二个参数

第二个参数主要是传递相关自定义变量的，类似于 `web()` 中的 `_config` / `_method` 等参数。

例如，如果你想在回调的时候使用非默认配置，则可以 `Pay::alipay()->callback(null, ['_config' => 'yansongda'])` 切换为 `yansongda` 这个租户的配置信息。

## 应用网关验证（ISV）

如果你是 ISV/代理商，在支付宝开放平台的第三方应用中配置了「应用网关 URL」，支付宝会向该地址主动发送请求：

- `EventType=verifygw`：应用网关验证请求，需要你返回由自身私钥签名、`biz_content` 携带应用公钥的 XML 应答，否则开放平台会报「网关地址和公钥验证失败」，无法完成网关配置；
- 其他 `EventType`（如 `follow` 等）：为发送给你的正式网关消息，验签通过后交由业务处理。

普通商户的自用型应用（非第三方应用）不会收到此类请求，无需关注本节。

### 用法

```php
Pay::config($this->config);

// $request 为 PSR-7 ServerRequestInterface，也可以自行解析后直接传 array
$response = Pay::alipay()->callback($request->all(), ['_action' => 'gw']);

return $response;
```

返回值可能是以下两种：

- `Psr\Http\Message\ResponseInterface`：当 `EventType=verifygw` 时，SDK 已自动验签并构造好签名的 XML 应答（HTTP 200，`Content-Type: text/xml;charset=utf-8`，`biz_content` 为你配置的应用公钥），直接 `return $response;` 交由框架回吐给支付宝即可；
- `Yansongda\Supports\Collection`：其他 `EventType` 的网关消息，SDK 完成验签后原样透传，由业务侧自行处理。**注意：官方要求业务侧回吐 ack XML 应答**（形如 `<XML><ToUserId/><AppId/><CreateTime/><MsgType>ack</MsgType></XML>`），SDK 本期不做自动应答，请自行构造并返回。

### `_action` 传递位置

| 入口形态 | `_action` 写法 |
|---|---|
| 业务参数数组 `callback(array_merge($form, ['_action' => 'gw']))` | 可写在第一参数数组内 |
| 第二实参 `callback(null, ['_action' => 'gw', '_config' => 'isv'])` | 写在第二参数 |
| **webhook 转发** `callback(['body' => ..., 'headers' => ...], ...)` | **只能写在第二实参**；写在外层或 `body` 内会被丢弃或污染验签组串 |

webhook 示例：

```php
$response = Pay::alipay()->callback(
    ['body' => $rawBody, 'headers' => $headers],
    ['_config' => 'isv', '_action' => 'gw']
);

return $response;
```

### 注意

- 验签失败**不会抛出异常**，而是返回 `success=false`、`error_code=VERIFY_FAILED` 的 XML 应答（同样附应用公钥，类型仍为 `Psr\Http\Message\ResponseInterface`）；
- 应用网关验签的组串规则与普通异步通知的唯一差异是**保留 `sign_type` 参数**（仅剔除 `sign`）；
- 应答固定使用 UTF-8 编码；
- 仅支持**公钥证书模式**配置（`app_public_cert_path` 等）；
- `VERIFY_FAILED` 应答同样依赖完整配置（`app_secret_cert`/`app_public_cert_path`），配置缺失时会抛出 `InvalidConfigException`；
- 应答 `openssl_sign` 失败时抛出 `InvalidConfigException`，不会静默产出空 `<sign>`。
