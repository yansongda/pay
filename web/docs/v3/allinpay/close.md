# 通联支付关闭订单

|    method     |     说明     |      参数      |    返回值     |
|:-----------:|:----------:|:------------:|:----------:|
|    close    |  交易关单   | array $order | Collection |
| nativeClose | 主扫交易关单 | array $order | Collection |

## 例子

```php
Pay::config($this->config);

// 关闭处理中的交易（/tranx/close）
$result = Pay::allinpay()->close([
    'oldreqsn' => 'order-1001', // 与 oldtrxid 二选一
]);

// 使主扫（native）下单生成的二维码即时失效（/unitorder/closenative）
$result = Pay::allinpay()->nativeClose([
    'oldreqsn' => 'order-1003', // 置二维码失效必填
]);

return $result->trxstatus;
```

## 订单配置参数

**所有订单配置中，客观参数均不用配置，扩展包已经为大家自动处理了**。

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://prodoc.allinpay.com/doc/983/)，查看「请求参数」一栏。

:::warning
订单生成后不能马上调用关单接口，建议最短调用时间间隔为 5 分钟，
时间间隔过短容易导致错账；关单前需确认支付状态。
:::
