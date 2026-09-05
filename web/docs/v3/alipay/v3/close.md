# 支付宝 V3 交易关闭

|  method  |   说明   |      参数      |   返回值    |
|:--------:|:------:|:------------:|:----------:|
|  close   | 交易关闭 | array $order | Collection |

## 例子

```php
Pay::config($this->config);

$result = Pay::alipay()->close([
    'out_trade_no' => '1514027114',
    // 'trade_no' => '2013112011001004330000121536', // 支付宝交易号，与 out_trade_no 二选一
]);
```

## 订单配置参数

所有订单配置参数和官方 V3 接口请求体无任何差别（snake_case 直传，无需 `method`/`biz_content` 等包装），例如 `out_trade_no`、`trade_no`、`operator_id` 等，请参考官方 PHP SDK 的 [`AlipayTradeCloseModel`](https://github.com/alipay/alipay-sdk-php-all) 查看完整参数。

`notify_url` 为该接口的官方 Model 独有字段，支持两级配置：订单参数传入优先，否则回落租户配置的 `notify_url`；两者均未提供时不会注入该字段。

## 返回值

返回 `Collection`，字段与支付宝官方 V3 接口响应体一致（无包裹层）。
