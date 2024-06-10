# 抖音支付

抖音支付目前直接内置支持以下快捷方式支付方法，对应的支付 method 如下：

|  method  |   说明   |      参数      |    返回值     |
|:--------:|:------:|:------------:|:----------:|
|   mini   | 小程序支付  | array $order | Collection |

## 小程序支付

### 例子

```php
Pay::config($config);

$order = [
    'out_order_no' => date('YmdHis') . rand(1000, 9999),
    'total_amount' => 1,
    'subject' => '闫嵩达 - test - subject - 01',
    'body' => '闫嵩达 - test - body - 01',
    'valid_time' => 600,
];

$result = Pay::douyin()->mini($order);
// 可直接通过 $result->order_id, $result->order_token 获取相关值。
// 后续调用不在本文档讨论范围内，请自行参考官方文档。
```

### 订单配置参数

**所有订单配置中，客观参数均不用配置，扩展包已经为大家自动处理了**，比如，`app_id`，`sign` 等参数，大家只需传入订单类主观参数即可。

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://developer.open-douyin.com/docs/resource/zh-CN/mini-app/develop/server/ecpay/pay-list/pay)，查看「请求参数」一栏。

### 调用支付

后续调起支付不再本文档讨论范围内，请参考[官方文档](https://developer.open-douyin.com/docs/resource/zh-CN/mini-app/develop/server/ecpay/pay-list/tt-pay)
