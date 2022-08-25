# 微信接收回调

|  方法名   | 参数  |    返回值     |
|:------:|:---:|:----------:|
| verify |  无  | Collection |

## 支付异步通知验证

```php
$result = $wechat->verify();
// 是的，你没有看错，就是这么简单！

// return $wechat->success()->send(); // laravel 框架直接 return $wechat->success();
```


## 退款异步通知验证

:::tip
v2.4.0 及以上可用
:::

```php
$result = $wechat->verify(null, true);
// 是的，你没有看错，就是这么简单！

// return $wechat->success()->send(); // laravel 框架直接 return $wechat->success();
```


## 配置参数

无


## 返回值

返回 Collection 类型，可以通过 `$collection->xxx` 得到服务器返回的数据。
