# Apple 查询

| 方法名 | 参数 | 返回值 |
|:---:|:---:|:---:|
| query | array $order | Collection |

`query` 通过 `_action` 参数支持三种查询：

| _action | 说明 | 端点 |
|:---:|:---:|:---:|
| transaction（默认） | 单笔交易 | GET /inApps/v1/transactions/{transactionId} |
| history | 交易历史 | GET /inApps/v2/history/{anyTransactionId} |
| subscriptions | 订阅状态 | GET /inApps/v1/subscriptions/{anyTransactionId}?status=1&status=4 |

## 单笔交易

```php
Pay::config($this->config);

$result = Pay::apple()->query([
    'transaction_id' => '1234567890',
]);
```

## 交易历史

```php
Pay::config($this->config);

$result = Pay::apple()->query([
    'transaction_id' => '1234567890',
    '_action' => 'history',
]);
```

## 订阅状态

```php
Pay::config($this->config);

$result = Pay::apple()->query([
    'transaction_id' => '1234567890',
    '_action' => 'subscriptions',
]);
```

默认查询 `status=1&status=4`（活跃 + 账单宽限期，与官方文档示例一致）。可通过 `_status` 覆盖，仅允许 1-5 的整数：

```php
$result = Pay::apple()->query([
    'transaction_id' => '1234567890',
    '_action' => 'subscriptions',
    '_status' => [1, 2],   // 1=活跃 2=已过期 3=账单重试 4=账单宽限期 5=已撤销
]);
```
