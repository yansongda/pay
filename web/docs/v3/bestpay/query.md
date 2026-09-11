# 翼支付查询订单

|  method |    说明    |      参数      |    返回值     |
|:------:|:--------:|:------------:|:----------:|
| query  | 订单查询 | array $order | Collection |

## 超级收银台订单查询（默认）

默认走超级收银台（1008）`/integrate/orderQuery`：

```php
Pay::config($this->config);

$result = Pay::bestpay()->query([
    'outTradeNo' => '1514027114', // 与 tradeNo 二选一，商户订单号
]);

return $result->get('result.tradeStatus');
```

## 线下聚合订单查询（_action=aggregate）

线下聚合（1006）订单需通过 `_action` 分流到 `/aggregate/aggregatepay/tradeQuery`：

```php
Pay::config($this->config);

$result = Pay::bestpay()->query([
    'outTradeNo' => '1514027114',
    '_action' => 'aggregate', // 线下聚合订单查询
]);

return $result->get('result.tradeStatus');
```

## 订单配置参数

**所有订单配置中，客观参数均不用配置，扩展包已经为大家自动处理了**。

所有订单配置参数和官方无任何差别，兼容所有功能，具体字段请以翼支付开发者文档
[render.bestpay.cn/open-developers](https://render.bestpay.cn/open-developers/index.html) 为准（需商户登录）。
