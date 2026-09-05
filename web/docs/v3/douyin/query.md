# 抖音查询订单

|  方法名  |      参数      |    返回值     |
|:-----:|:------------:|:----------:|
| query | array $order | Collection |

抖音通用交易系统的查询通过 `_action` 参数分发，支持查询订单、CPS 信息与退款单：

|    `_action`     |   说明    |              对应官方 API               |
|:----------------:|:-------:|:------------------------------------:|
| `order`（默认） | 查询订单  |  `/api/trade_basic/v1/developer/order_query/`  |
|      `cps`       | 查询 CPS  |   `/api/trade_basic/v1/developer/query_cps/`   |
|     `refund`     | 查询退款单  |  `/api/trade_basic/v1/developer/refund_query/` |

## 查询支付订单

```php
Pay::config($config);

$order = [
    'out_order_no' => '202408040747147327',
    // '_action' => 'order', // 查询订单，默认
];

$result = Pay::douyin()->query($order);
```

### 订单配置参数

`order_id`（抖音侧订单号）与 `out_order_no`（商户侧订单号）**二选一**必填，其余参数和官方无任何差别，兼容所有功能，所有参数请参考以下 API 查看「请求参数」一栏。

- [查询订单](https://developer.open-douyin.com/docs/resource/zh-CN/mini-app/develop/server/payment/trade-system/general/order/query-order)

## 查询 CPS 信息

```php
Pay::config($config);

$order = [
    'out_order_no' => '202408040747147327',
    '_action' => 'cps',
];

$result = Pay::douyin()->query($order);
```

### 订单配置参数

`order_id` 与 `out_order_no` **二选一**必填，所有参数请参考以下 API 查看「请求参数」一栏。

- [查询 CPS](https://developer.open-douyin.com/docs/resource/zh-CN/mini-app/develop/server/payment/trade-system/general/order/query-cps)

## 查询退款订单

```php
Pay::config($config);

$order = [
    'out_refund_no' => '202408040747147327R',
    '_action' => 'refund',
];

$result = Pay::douyin()->query($order);
```

### 订单配置参数

`refund_id`（抖音侧退款单号）、`out_refund_no`（商户侧退款单号）、`order_id`（抖音侧订单号，按订单查询上限 50 条）**三选一**必填，所有参数请参考以下 API 查看「请求参数」一栏。

- [查询退款](https://developer.open-douyin.com/docs/resource/zh-CN/mini-app/develop/server/payment/trade-system/general/refund/query-refund)
