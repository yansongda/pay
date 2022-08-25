# 支付宝下载对账单

|   方法名    |         参数         |  返回值   |
|:--------:|:------------------:|:------:|
| download | string/array $bill | string |

## 例子

```php
$bill = [
    'bill_date' => '2016-04-05',    // 2016-04
    'bill_type' => 'trade'
];

// $bill = '2016-04-05';

$url = $alipay->download($bill);
```

## 配置参数

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://docs.open.alipay.com/api_15/alipay.data.dataservice.bill.downloadurl.query)，查看「请求参数」一栏。


## 返回值

返回 string 类型。直接返回账单下载链接。
