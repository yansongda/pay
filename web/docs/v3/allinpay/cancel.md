# 通联支付撤销

|   method   |   说明   |      参数      |    返回值     |
|:--------:|:------:|:------------:|:----------:|
|  cancel  |  撤销   | array $order | Collection |

## 例子

```php
Pay::config($this->config);

$result = Pay::allinpay()->cancel([
    'reqsn'    => 'cancel-1', // 商户撤销交易单号，商户平台唯一
    'trxamt'   => 1, // 原订单金额
    'oldtrxid' => '240101120000000001', // 原交易流水，与 oldreqsn 二选一
]);

return $result->trxstatus;
```

## 订单配置参数

**所有订单配置中，客观参数均不用配置，扩展包已经为大家自动处理了**。

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://prodoc.allinpay.com/doc/314/)，查看「请求参数」一栏。

:::warning
撤销接口只能撤销**当天的交易**，且为全额退款、实时返回退款结果；
调用撤销接口异常时，请先查询原支付订单状态。
:::
