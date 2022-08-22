# 日志系统

## 配置

### 使用内置日志系统

:::tip
使用前，请确保已经安装了 `monolog/monolog`: `composer require monolog/monolog`
:::

SDK 自带日志系统，如果需要指定日志文件或日志级别，请 config 中传入下列参数。
如果不传入，则日志系统默认不启用。

```php
'logger' => [
    'enable' => true,
    'file' => './logs/pay.log', // 请注意权限
    'level' => 'info', // 建议生产环境等级调整为 info，开发环境为 debug
    'type' => 'single', // optional, 可选 daily， daily 时将按时间自动划分文件.
    'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
],
```

### 使用外部日志系统

yansongda/pay 支持使用外部日志系统，只需要日志规范支持 PSR3 规范，实现了 `\Psr\Log\LoggerInterface` 即可。

```php
use Yansongda\Pay\Contract\LoggerInterface;

// $logger = 你的日志系统

Pay::set(LoggerInterface::class, $logger);
```

## 使用

:::tip
使用日志功能前，请先确认已经使用过支付等功能进行了初始化！
:::

```php
use Yansongda\Pay\Logger;

Logger::debug('Paying...', $order->all());
```
