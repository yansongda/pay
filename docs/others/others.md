# 日志

SDK 自带日志系统，如果需要指定日志文件或日志级别，请 config 中传入下列参数。如果不传入，默认为 `warning` 级别，日志路径在 `sys_get_temp_dir().'/logs/yansongda.pay.log' `

```php
'log' => [
    'file' => './logs/pay.log', // 请注意权限
    'level' => 'debug'
],
```

## 使用日志功能

> 使用日志功能前，请先确认已经使用过支付等功能进行了初始化！

```php
use Yansongda\Pay\Log;

Log::debug('Paying...', $order->all());
```

# 支持的模式

| 支付 | 模式 | 说明 |
| :---: | :---: | :---: |
| alipay | dev | 沙箱模式 |
| wechat | dev | 沙箱模式 |
| wechat | hk | 东南亚节点 |

## 沙箱模式

支付宝及微信均提供了沙箱测试模式，如果需要启动，请 config 中传入下列参数。

```php
['mode' => 'dev']
```

### 关于微信沙箱模式

微信沙箱模式与支付宝沙箱模式不同，也没有支付宝沙箱模式那样简单，SDK 只对微信支付 API 进行了沙箱处理，所以，在测试微信时，推荐直接使用正式环境 *￥0.01* 进行测试。

详细请参考 [https://github.com/yansongda/pay/issues/62](https://github.com/yansongda/pay/issues/62)