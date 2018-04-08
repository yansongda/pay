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
use Hanwenbo\Pay\Log;

Log::debug('Paying...', $order->all());
```

# 支持的模式

| 支付 | 模式 | 说明 |
| :---: | :---: | :---: |
| alipay | dev | 沙箱模式 |
| wechat | dev | 沙箱模式 |
| wechat | hk | 东南亚节点 |
| wechat | service | 服务商模式 |

## 沙箱模式

支付宝及微信均提供了沙箱测试模式，如果需要启动，请 config 中传入下列参数。

```php
['mode' => 'dev']
```

### 关于微信沙箱模式

微信沙箱模式与支付宝沙箱模式不同，也没有支付宝沙箱模式那样简单，SDK 只对微信支付 API 进行了沙箱处理，所以，在测试微信时，推荐直接使用正式环境 *￥0.01* 进行测试，随后再进行退款，这样，两个功能都可以测试到。

详细请参考 [https://github.com/yansongda/pay/issues/62](https://github.com/yansongda/pay/issues/62)

## 微信服务商模式

> 版本要求: version >= 2.1.0

config 配置参数如下。

```php
$config = [
    'appid' => 'wxb3fxxxxxxxxxxx', // APP APPID
    'app_id' => 'wxb3fxxxxxxxxxxx', // 公众号 APPID
    'miniapp_id' => 'wxb3fxxxxxxxxxxx', // 小程序 APPID
    'sub_appid' => 'wxb3fxxxxxxxxxxx', // 子商户 APP APPID
    'sub_app_id' => 'wxb3fxxxxxxxxxxx', // 子商户 公众号 APPID
    'sub_miniapp_id' => 'wxb3fxxxxxxxxxxx', // 子商户 小程序 APPID
    'mch_id' => '146xxxxxx', // 商户号
    'sub_mch_id' => '146xxxxxx', // 子商户商户号
    'key' => '4e538260xxxxxxxxxxxxxxxxxxxxxx', // 主商户 key
    'notify_url' => 'http://yanda.net.cn/notify.php',
    'cert_client' => './cert/apiclient_cert.pem', // optional，退款等情况时用到
    'cert_key' => './cert/apiclient_key.pem',// optional，退款等情况时用到
    'log' => [ // optional
        'file' => './logs/wechat.log',
        'level' => 'debug'
    ],
    'mode' => 'service',
]
```

**说明：** 处于服务商模式下的时候，`appid`、`app_id`、`miniapp_id` 均为**主商户**的信息，`sub_` 开头的为**子服务商**的信息

详细请参考 [https://github.com/yansongda/pay/pull/82](https://github.com/yansongda/pay/pull/82)
