# 抖音快速入门

在初始化完毕后，就可以直接方便的享受 `yansongda/pay`  带来的便利了。

## 小程序支付

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
