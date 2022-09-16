# 接收银联回调

|   方法名    |               参数               |    返回值     |
|:--------:|:------------------------------:|:----------:|
| callback | 无/array/ServerRequestInterface | Collection |

## 例子

```php
Pay::config($this->config);

// 是的，你没有看错，就是这么简单！
$result = Pay::unipay()->callback();
```

## 参数

### 第一个参数

#### `null`

如果您没有传参，或传 `null` 则 `yansongda/pay` 会自动识别支付宝的回调请求并处理，通过 `Collection` 实例返回支付宝的处理参数

:::warning
建议仅在 php-fpm 下使用，swoole 方式请使用 `ServerRequestInterface` 参数传递方式
:::

#### `ServerRequestInterface`

推荐在 swoole 环境下传递此参数，传递此参数后， yansongda/pay 会自动进行后续处理

#### `array`

也可以自行解析请求参数，传递一个 array 会自动进行后续处理

### 第二个参数

第二个参数主要是传递相关自定义变量的，类似于 `web()` 中的 `_config` / `_method` 等参数。

例如，如果你想在回调的时候使用非默认配置，则可以 `Pay::unipay()->callback(null, ['_config' => 'yansongda'])` 切换为 `yansongda` 这个租户的配置信息。
