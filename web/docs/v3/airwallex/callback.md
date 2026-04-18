# 接收 Airwallex 回调

> 官方文档：
> [Listen for webhook events](https://www.airwallex.com/docs/developer-tools/webhooks/listen-for-webhook-events)


| 方法名 | 参数 | 返回值 |
|:--:|:--:|:--:|
| callback | null/array/ServerRequestInterface | Collection |

:::warning
Airwallex 回调处理会自动校验签名。

请务必在配置中填写 `webhook_secret`，并使用回调原始 Body 完成验签。
签名相关头信息为：

- `x-timestamp`
- `x-signature`
:::

## 例子

```php
Pay::config($config);

$result = Pay::airwallex()->callback();
```

## 参数

### 第一个参数

#### `null`

不传或传 `null` 时，`yansongda/pay` 会自动识别当前请求并处理。

#### `ServerRequestInterface`

推荐在 swoole / hyperf 等常驻内存环境中显式传入：

```php
$result = Pay::airwallex()->callback($request);
```

#### `array`

也可以自行解析请求后传入：

```php
$result = Pay::airwallex()->callback([
    'headers' => $request->getHeaders(),
    'body' => (string) $request->getBody(),
]);
```

### 第二个参数

第二个参数用于传递运行时附加参数，比如切换租户配置：

```php
$result = Pay::airwallex()->callback(null, [
    '_config' => 'other',
]);
```
