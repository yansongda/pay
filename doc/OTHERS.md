# 日志

SDK 自带日志系统，如果需要指定日志文件或日志级别，请 config 中传入下列参数。如果不传入，默认为 `warning` 级别，日志路径在 `sys_get_temp_dir().'/logs/yansongda.pay.log' `

```php
'log' => [
    'file' => './logs/pay.log', // 请注意权限
    'level' => 'debug'
],
```

## 使用日志功能

```php
use Yansongda\Pay\Log;

Log::debug('Paying...', $order->all());
```

# 沙箱模式

支付宝及微信均提供了沙箱测试模式，如果需要启动，请 config 中传入下列参数。

```php
'mode' => 'dev',
```

## 支持的模式

| 支付 | 模式 | 说明 |
| :---: | :---: | :---: |
| alipay | dev | 沙箱模式 |
| wechat | dev | 沙箱模式 |
| wechat | hk | 东南亚节点 |
