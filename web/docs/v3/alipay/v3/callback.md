# 接收支付宝回调

|   方法名    |               参数               |    返回值     |
|:--------:|:------------------------------:|:----------:|
| callback | 无/array/ServerRequestInterface | Collection |

支付宝 trade 异步通知为 form 参数格式（无论触发通知的接口走 V2 还是 V3 管道，报文格式相同），SDK 统一自动完成验签——除去 `sign`/`sign_type` 后按字典序组串、RSA2 验签，使用租户配置的支付宝公钥证书，且**验签强制执行、无法关闭**。

验签通过后返回 `Collection`，内容为完整的通知参数（含 `sign`/`sign_type`）；应答为字面量 `success`（与 V2 相同）。

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

也可以自行解析请求参数后传递一个 array（form 参数键值对），SDK 会将其作为通知参数进行后续处理；此外还支持 `{ 'body': '...', 'headers': [...] }` 形态（webhook 转发场景），SDK 会自动解析 form 串。

### 第二个参数

第二个参数主要用来传递相关自定义变量，例如在回调时切换租户：`Pay::alipay()->callback($request, ['_config' => 'page'])` 会切换为 `page` 租户的配置进行验签。

:::tip
多租户场景下，不同租户的 `notify_url` 建议按业务分开配置；共用回调入口时，可按通知参数（如 `app_id`）自行分流后，通过 `callback($request, ['_config' => 'xxx'])` 指定对应租户验签。
:::

## 事件

`CallbackReceived` 事件携带的 `$contents` 参数为**解析后的通知参数数组**（V2/V3 统一）。事件的使用方式请参考 [事件](/docs/v3/others/event.md)。

## 应用回调

应用回调（`appCallback()`）为 APP 支付（V2 管道）专属，用法与 V2 一致，请参考 [确认回调](/docs/v3/alipay/response.md)。
