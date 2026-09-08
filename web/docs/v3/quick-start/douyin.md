# 抖音快速入门

在初始化完毕后，就可以直接方便的享受 `yansongda/pay`  带来的便利了。

:::tip
`yansongda/pay` v3.8.0-beta.6 起接入抖音「通用交易系统」，老的「担保支付」（ecpay）已删除，详细变更与迁移说明请参考 [v3.8 升级指南](/docs/v3/upgrade/v3.8.md)。
:::

## 小程序支付

```php
Pay::config($config);

$order = [
    'outOrderNo' => date('YmdHis') . rand(1000, 9999),
    'totalAmount' => 1,
    'skuList' => [
        [
            'skuId' => 'sku-001',
            'title' => '闫嵩达 - test - subject - 01',
            'quantity' => 1,
            'price' => 1,
        ],
    ],
    'orderEntrySchema' => [
        'path' => 'pages/order/detail',
        'params' => '{"out_order_no":"202408040747147327"}',
    ],
];

$result = Pay::douyin()->mini($order);
// 返回 $result->data 与 $result->byteAuthorization 两个值，原样传给前端调用 tt.requestOrder 即可完成下单。
// 后续调用不在本文档讨论范围内，请自行参考官方文档。
```

:::warning 沙盒环境说明
`trade_basic` 业务接口**无沙盒环境**；`MODE_SANDBOX` 会把全部请求（含业务接口）指向 `open-sandbox.douyin.com`，该域名并无 `trade_basic` 部署，仅适合验证 `client_token`。
:::
