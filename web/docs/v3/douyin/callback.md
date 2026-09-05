# 接收抖音回调

抖音通用交易系统提供三类回调，SDK 分别提供三个入口方法，**均需要传入 `ServerRequestInterface`**（验签依赖请求头中的 `Byte-*` 回调头信息）：

|          方法名           |              参数              |    返回值     |              对应回调类型               |
|:-----------------------:|:----------------------------:|:----------:|:---------------------------------:|
|        callback         | 无/ServerRequestInterface/array | Collection |      `payment` 支付结果回调      |
|      refundCallback     |      ServerRequestInterface       | Collection |       `refund` 退款结果回调        |
|    preRefundCallback    |      ServerRequestInterface       | Collection | `pre_create_refund` 退款申请回调 |

:::danger 未经实测
以下回调相关说明基于抖音官方文档静态整理，**尚未经真实商户环境实测**，联调时如遇签名/字段差异，请以官方文档为准并[提交 issue](https://github.com/yansongda/pay/issues) 反馈。
:::

## 例子

### 支付结果回调

```php
use Psr\Http\Message\ServerRequestInterface;

Pay::config($this->config);

// 是的，你没有看错，就是这么简单！
// $request 为 PSR-7 标准的当前请求实例
$result = Pay::douyin()->callback($request);
// $result 中为支付结果信息（平台公钥验签 + 解析 msg 后的 Collection）
```

### 退款结果回调

```php
use Psr\Http\Message\ServerRequestInterface;

Pay::config($this->config);

$result = Pay::douyin()->refundCallback($request);
```

### 退款申请回调

```php
use Psr\Http\Message\ServerRequestInterface;

Pay::config($this->config);

$result = Pay::douyin()->preRefundCallback($request);
// 退款申请回调需要业务方自行构造同步应答，请参考：退款文档中的「响应退款申请回调」章节
```

## 验签说明

三类回调统一为 JSON body：`{"version": "3.0", "type": "payment|refund|pre_create_refund", "msg": "<JSON字符串>"}`，验签信息在**请求头**：

|     请求头      |     说明     |
|:-------------:|:----------:|
| Byte-Timestamp |  时间戳（秒）  |
| Byte-Nonce-Str |   随机字符串   |
| Byte-Signature | Base64 签名值 |

- 验签串共三行，每行末尾以 `\n` 结尾：`{Byte-Timestamp}\n{Byte-Nonce-Str}\n{原始body}\n`
- 使用配置的 `platform_public_key`（抖音平台公钥）以 SHA256withRSA 校验，即 `SHA256withRSA(验签串, platform_public_key) === Base64Decode(Byte-Signature)`
- **必须使用未经解析的原始 body 字符串参与验签**，官方明确：body 经框架解析/排序后验签必失败。SDK 从 `ServerRequestInterface` 中取原始 body，因此请将框架原样的 PSR-7 请求传入，不要自行解析后重建请求

每个回调入口会严格校验 body 顶层 `type` 字段与入口语义匹配（`payment`/`refund`/`pre_create_refund`），不匹配将抛出异常，防止误接线导致平台无限重试卡单。

## 参数

### 第一个参数

#### `ServerRequestInterface`

**推荐方式**。传递 PSR-7 标准的当前请求实例后，yansongda/pay 会自动完成验签与解析处理，通过 `Collection` 实例返回回调内容。

#### `null`（仅 `callback` 支持）

如果您没有传参，或传 `null`，则 yansongda/pay 会通过 `ServerRequest::fromGlobals()` 自动获取当前请求并处理。

:::warning
建议仅在 php-fpm 下使用，swoole 方式请使用 `ServerRequestInterface` 参数传递方式
:::

#### `array`（仅 `callback` 签名上保留，实际不可用）

受 `ProviderInterface` 接口约束，`callback()` 保留了 `array` 入参的宽签名，但抖音通用交易系统的回调验签**依赖请求头中的 `Byte-*` 回调头信息**，自行解析出的数组无法携带这些信息，因此传 `array` 会直接抛出异常。

#### `refundCallback()` / `preRefundCallback()`

仅支持 `ServerRequestInterface` 入参（第一个参数），否则抛出异常。

### 第二个参数

第二个参数主要是传递相关自定义变量的，类似于 `web()` 中的 `_config` / `_method` 等参数。

例如，如果你想在回调的时候使用非默认配置，则可以 `Pay::douyin()->callback($request, ['_config' => 'yansongda'])` 切换为 `yansongda` 这个租户的配置信息。
