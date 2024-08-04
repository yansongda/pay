# 抖音退款

|  方法名   |      参数      |    返回值     |
|:------:|:------------:|:----------:|
| refund | array $order | Collection |

## 退款操作

```php
Pay::config($config);

$order = [
    'out_order_no' => '202408040747147327',
    'out_refund_no' => '202408040747147327',
    'reason' => '测试',
    'refund_amount' => 1,
    // '_action' => 'mini', // 小程序退款，默认

];

$result = Pay::douyin()->refund($order);
```

### 订单配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考以下 API 查看「请求参数」一栏。

- [小程序订单](https://developer.open-douyin.com/docs/resource/zh-CN/mini-app/develop/server/ecpay/refund-list/refund)
