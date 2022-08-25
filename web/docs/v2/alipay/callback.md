# 支付宝接收回调

|  方法名   | 参数  |    返回值     |
|:------:|:---:|:----------:|
| verify |  无  | Collection |

使用的加密方式为支付宝官方推荐的 **RSA2**，目前只支持这一种加密方式，且没有支持其他加密方式的计划。

## 例子

```php
$result = $alipay->verify();
// 是的，你没有看错，就是这么简单！

// return $alipay->success()->send(); // laravel 框架直接 return $alipay->success();
```

## 配置参数

无


## 返回值

返回 Collection 类型，可以通过 `$collection->xxx` 得到服务器返回的数据。
