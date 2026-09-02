# 接收支付宝回调（V3）

|   方法名    |               参数               |    返回值     |
|:--------:|:------------------------------:|:----------:|
| callback | 无/array/ServerRequestInterface | Collection |

`version` 配置为 `v3` 的租户调用 `callback()` 时，行为与 V2 大体一致，需要注意以下三点：

1. **异步通知格式与 V2 相同**：支付宝 trade 异步通知本身仍为 form 参数格式（并非 JSON body + header 签名），SDK 会自动完成验签——除去 `sign`/`sign_type` 后按字典序组串、RSA2 验签，且验签强制执行、无法关闭；
2. **验签通过后返回 `Collection`**：内容为完整的通知参数（含 `sign`/`sign_type`）；
3. **应答为字面量 `success`**（与 V2 相同）。

## 例子

推荐使用 `ServerRequestInterface` 传参（swoole 等常驻内存环境必须使用该方式）：

```php
use Psr\Http\Message\ServerRequestInterface;

Pay::config($this->config);

// $request 为 PSR-7 ServerRequestInterface实例
// 是的，你没有看错，就是这么简单！验签已自动完成
$result = Pay::alipay()->callback($request);

return Pay::alipay()->success();
```

### 第一个参数

#### `null`

如果没有传参，或传 `null`，则 SDK 会自动识别当前的回调请求并处理（`ServerRequest::fromGlobals()`）。

:::warning
建议仅在 php-fpm 下使用，swoole 方式请使用 `ServerRequestInterface` 参数传递方式
:::

#### `ServerRequestInterface`

推荐传递此参数，传递后 SDK 会自动进行后续处理。

#### `array`

也可以自行解析请求参数后传递一个 array（form 参数键值对），SDK 会将其构造为模拟的回调请求再进行后续处理。

### 第二个参数

第二个参数主要用来传递相关自定义变量，例如在回调时切换租户：`Pay::alipay()->callback($request, ['_config' => 'page'])` 会切换为 `page` 租户的配置进行验签。

## 共用 notify_url 的注意点

V2 与 V3 的异步通知格式相同（均为 form 参数），但**验签所用的租户配置不同**（V2 为证书模式，V3 为公钥或证书模式）。如果 V2 页面类接口与 V3 服务端接口共用同一 `notify_url`，同一回调入口收到的通知无法由 SDK 自动区分应由哪个租户验签，需要注意：

- 最简单的方式：V2 与 V3 按业务**分开配置** `notify_url`；
- 共用回调入口时：自行分流（例如按通知参数中的 `app_id` 区分）后，通过 `callback($request, ['_config' => 'xxx'])` 指定对应租户。

多租户的完整用法请参考 [多租户逃生通道](/docs/v3/alipay/v3/index.md#多租户逃生通道)。

## 事件形态差异

`CallbackReceived` 事件携带的 `$contents` 参数在 V2 与 V3 分支形态不同：

| 版本 | `$contents` 形态 |
|:--:|:--------------:|
| V2 | 数组（解析后的通知参数） |
| V3 | `ServerRequestInterface`（原始回调请求实例） |

事件监听方如需同时支持 V2/V3 租户，请按形态区分处理。事件的使用方式请参考 [事件](/docs/v3/others/event.md)。

## 应用回调

V3 暂不支持应用回调（`appCallback()`），调用会抛出异常（提示通过 `_config` 指向 V2 租户）。
