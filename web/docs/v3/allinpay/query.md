# 通联支付查询订单

|       method       |      说明      |      参数      |    返回值     |
|:-----------------:|:------------:|:------------:|:----------:|
|       query       |   交易查询     | array $order | Collection |
|    queryConfirm   | 交易确认查询   | array $order | Collection |

## 普通查询

```php
Pay::config($this->config);

$result = Pay::allinpay()->query([
    'reqsn' => 'order-1001', // 与 trxid 二选一，同时拥有时建议优先使用 trxid
]);

return $result->trxstatus;
```

## 确认查询

交易确认查询（`/tranx/queryconfirm`）具备查询性能高、延迟低的特点，仅支持查询 2 个自然日内的交易，
建议用于确认 `native`、JS 支付、小程序支付等异步类交易的最终结果：

```php
Pay::config($this->config);

$result = Pay::allinpay()->queryConfirm([
    'trxid' => '240101120000000001', // 与 reqsn 二选一
]);

return $result->trxstatus;
```

## 订单配置参数

**所有订单配置中，客观参数均不用配置，扩展包已经为大家自动处理了**。

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://prodoc.allinpay.com/doc/982/)，查看「请求参数」一栏。
