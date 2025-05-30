# 微信撤销接口

|  方法名  |      参数      | 返回值  |
|:-----:|:------------:|:----:|
| cancel | array $order | null |

## 撤销商家转账

```php
Pay::config($config);

$order = [
    'out_bill_no' => '1514027114',
    // '_action' => 'mch_transfer', // mch_transfer 撤销转账，默认
];

$result = Pay::wechat()->cancel($order);
```

### 订单配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考以下 API 查看「请求参数」一栏。

- [撤销转账](https://pay.weixin.qq.com/doc/v3/merchant/4012716458)

