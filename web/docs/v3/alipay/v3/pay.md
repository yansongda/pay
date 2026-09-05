# 支付宝 V3 支付

V3 支持以下快捷方式支付方法（`version` 配置为 `v3` 的租户）：

|  method  |       说明       |      参数      |    返回值    |
|:--------:|:--------------:|:------------:|:----------:|
|   pos    |  付款码支付（被扫码）  | array $order | Collection |
|   scan   | 扫码支付（预创建，主动扫） | array $order | Collection |

:::warning
页面类接口 `web` / `h5` / `app` / `mini` 在 V3 租户上暂不支持，调用会抛出异常。如需使用，请参考 [多租户逃生通道](/docs/v3/alipay/v3/index.md#多租户逃生通道)。
:::

## 付款码支付（刷卡支付，被扫码）

### 例子

```php
Pay::config($this->config);

$result = Pay::alipay()->pos([
    'out_trade_no' => ''.time(),
    'auth_code' => '284776044441477959',
    'total_amount' => '0.01',
    'subject' => 'yansongda 测试 - 01',
]);
```

### 订单配置参数

所有订单配置参数和官方 V3 接口请求体无任何差别（snake_case 直传，无需 `method`/`biz_content` 等包装），例如 `out_trade_no`、`auth_code`、`total_amount`、`subject` 等，请参考官方 PHP SDK 的 [`AlipayTradePayModel`](https://github.com/alipay/alipay-sdk-php-all) 查看完整参数。

`notify_url` 支持两级配置：订单参数传入优先，否则回落租户配置的 `notify_url`。

## 扫码支付（预创建，主动扫）

### 例子

```php
Pay::config($this->config);

$result = Pay::alipay()->scan([
    'out_trade_no' => ''.time(),
    'total_amount' => '0.01',
    'subject' => 'yansongda 测试 - 01',
]);

return $result->get('qr_code'); // 二维码 url
```

### 订单配置参数

所有订单配置参数和官方 V3 接口请求体无任何差别（snake_case 直传），例如 `out_trade_no`、`total_amount`、`subject` 等，请参考官方 PHP SDK 的 [`AlipayTradePrecreateModel`](https://github.com/alipay/alipay-sdk-php-all) 查看完整参数。

`notify_url` 支持两级配置：订单参数传入优先，否则回落租户配置的 `notify_url`。

## 返回值

以上接口均返回 `Collection`，字段与支付宝官方 V3 接口响应体一致（无包裹层）。例如付款码支付成功返回 `trade_no` 等字段，扫码支付返回 `qr_code` 字段。

用户自行实现第三方授权（服务模式）时，可通过订单参数 `'_app_auth_token' => 'xxx'` 或租户配置 `app_auth_token` 传递授权 token，SDK 会自动放入 `alipay-app-auth-token` 请求头并参与签名。
