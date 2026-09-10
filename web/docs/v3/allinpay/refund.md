# 通联支付退款

|   method   |   说明   |      参数      |    返回值     |
|:--------:|:------:|:------------:|:----------:|
|  refund  |  退款   | array $order | Collection |

## 例子

```php
Pay::config($this->config);

$result = Pay::allinpay()->refund([
    'reqsn'    => 'refund-1', // 商户退款订单号，商户平台唯一
    'trxamt'   => 1, // 退款金额，单位：分，支持部分退款
    'oldreqsn' => 'order-1001', // 原交易订单号，与 oldtrxid 二选一
]);

return $result->trxstatus;
```

## 订单配置参数

**所有订单配置中，客观参数均不用配置，扩展包已经为大家自动处理了**。

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://prodoc.allinpay.com/doc/258/)，查看「请求参数」一栏。

:::warning
建议在交易完成后间隔几分钟（最短 5 分钟）再调用退款接口，避免出现订单状态同步不及时导致退款失败；
云闪付（银联扫码）含单品优惠交易只能整单退款，不支持部分退款。
:::
