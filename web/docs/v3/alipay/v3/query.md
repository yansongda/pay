# 支付宝 V3 交易查询

|  method  |   说明   |      参数      |    返回值    |
|:--------:|:------:|:------------:|:----------:|
|  query   | 交易查询 | array $order | Collection |

## 例子

```php
Pay::config($this->config);

$result = Pay::alipay()->query([
    'out_trade_no' => '1514027114',
    // 'trade_no' => '2013112011001004330000121536', // 支付宝交易号，与 out_trade_no 二选一
]);

return $result->get('trade_status'); // 交易状态
```

## 订单配置参数

所有订单配置参数和官方 V3 接口请求体无任何差别（snake_case 直传，无需 `method`/`biz_content` 等包装），例如 `out_trade_no`、`trade_no` 等，请参考官方 PHP SDK 的 [`AlipayTradeQueryModel`](https://github.com/alipay/alipay-sdk-php-all) 查看完整参数。

## 返回值

返回 `Collection`，字段与支付宝官方 V3 接口响应体一致（无包裹层），例如 `trade_no`、`out_trade_no`、`trade_status` 等。
