# 微信支付分

微信支付分通过 `Pay::wechat()->payscore()` 快捷方式调用，使用 `_action` 参数区分具体动作，对应的 `_action` 与插件如下：

|     `_action`     |        插件        |            官方链接            |              说明              |
|:-----------------:|:------------------:|:------------------------------:|:------------------------------:|
|  create（缺省）  | `\Yansongda\Pay\Plugin\Wechat\V3\PayScore\CreatePlugin` | [创建支付分订单](https://pay.weixin.qq.com/doc/v3/merchant/4012587900) | 创建服务订单（POST /v3/payscore/serviceorder） |
|       query       | `\Yansongda\Pay\Plugin\Wechat\V3\PayScore\QueryPlugin` | [查询支付分订单](https://pay.weixin.qq.com/doc/v3/merchant/4012587902) | 查询支付分订单（`out_order_no` 与 `query_id` 二选一，互斥） |
|      cancel       | `\Yansongda\Pay\Plugin\Wechat\V3\PayScore\CancelPlugin` | [取消支付分订单](https://pay.weixin.qq.com/doc/v3/merchant/4012587905) | 取消支付分订单（POST /v3/payscore/serviceorder/{no}/cancel） |
|     complete      | `\Yansongda\Pay\Plugin\Wechat\V3\PayScore\CompletePlugin` | [完结支付分订单](https://pay.weixin.qq.com/doc/v3/merchant/4012587955) | 完结支付分订单（POST /v3/payscore/serviceorder/{no}/complete） |
|      modify       | `\Yansongda\Pay\Plugin\Wechat\V3\PayScore\ModifyPlugin` | [修改支付分订单金额](https://pay.weixin.qq.com/doc/v3/merchant/4012647427) | 修改订单金额（POST /v3/payscore/serviceorder/{no}/modify） |
|       sync        | `\Yansongda\Pay\Plugin\Wechat\V3\PayScore\SyncPlugin` | [同步支付分订单信息](https://pay.weixin.qq.com/doc/v3/merchant/4012587962) | 同步订单信息（POST /v3/payscore/serviceorder/{no}/sync） |
|        pay        | `\Yansongda\Pay\Plugin\Wechat\V3\PayScore\PayPlugin` | [催收扣款](https://pay.weixin.qq.com/doc/v3/merchant/4013394596) | 订单催收扣款（POST /v3/payscore/serviceorder/{no}/pay） |
|   permissions     | `\Yansongda\Pay\Plugin\Wechat\V3\PayScore\Permissions\CreatePlugin` | [商户预授权（签约）](https://pay.weixin.qq.com/doc/v3/merchant/4012647349) | 商户预授权（POST /v3/payscore/permissions） |
| permissionsQuery  | `\Yansongda\Pay\Plugin\Wechat\V3\PayScore\Permissions\QueryPlugin` | [查询用户授权记录（协议号）](https://pay.weixin.qq.com/doc/v3/merchant/4012647401) | 查询用户授权记录（GET /v3/payscore/permissions/authorization-code/{no}） |
| permissionsTerminate | `\Yansongda\Pay\Plugin\Wechat\V3\PayScore\Permissions\TerminatePlugin` | [解除授权（协议号）](https://pay.weixin.qq.com/doc/v3/merchant/4012647410) / [解除授权（openid）](https://pay.weixin.qq.com/doc/v3/merchant/4012647413) | 解除用户授权（**应答 204 无包体**，返回 `Response`） |

:::tip 使用前须知

1. SDK 入口为 `Pay::wechat()->payscore()`，通过 `_action` 参数区分具体动作，缺省为 `create`
2. 使用前需在微信商户平台开通「微信支付分」功能
3. `service_id`（支付分服务 ID）支持配置级或参数级传递，优先级：调用参数 > 配置文件；既未配置也未传参时，SDK 会抛出 `缺少支付分服务ID` 异常

   ```php
   Pay::config([
       'wechat' => [
           'default' => [
               // ... 其它微信配置
               // 「支付分必填」支付分服务 ID，在微信商户平台「支付分」栏目中查看
               'service_id' => '你的服务ID',
           ],
       ],
   ]);
   ```

   也可以在调用时通过参数级 `service_id` 覆盖：`['service_id' => '你的服务ID', ...]`
4. 目前仅支持直连商户模式，服务商模式暂不支持
:::

## 创建支付分订单

### 例子

```php
Pay::config($config);

$order = [
    '_action' => 'create', // 缺省动作，可省略
    'out_order_no' => time().'',
    'service_introduction' => '健身房-免费体验课',
    'time_range' => [
        'start_time' => '20260910103000', // 用户预计开始使用服务时间，格式 yyyyMMddHHmmss
        'end_time' => '20260910120000', // 选填，用户预计结束使用服务时间
    ],
    'risk_fund' => [
        'name' => 'ESTIMATE_ORDER_COST', // 风险金名称，详见官方文档
        'amount' => 10000, // 单位：分，须大于 0
        'description' => '订单的预估费用', // 选填
    ],
    // 'location' => [...], // 选填，使用服务地点
    // 'attach' => '附加数据', // 选填，商户自定义数据，回调时原样返回
];

$result = Pay::wechat()->payscore($order);
// 创建成功：$result->order_id 微信支付分订单ID；$result->package 用于前端调起
```

### 订单配置参数

`appid`、`service_id`、`notify_url` 由 SDK 自动注入（`appid` 默认取 `mp_app_id`，可通过 `_type` 切换；也可在参数中显式传递覆盖）。

所有订单配置参数和官方无任何差别，兼容所有功能，所有参数请参考[这里](https://pay.weixin.qq.com/doc/v3/merchant/4012587900)，查看「请求参数」一栏。

|     参数     | 必填 |                说明                |
|:------------:|:----:|:----------------------------------:|
| out_order_no |  ✅  |        商户服务订单号（32 字符内）        |
| service_introduction |  ✅  | 服务信息，不超过 20 个字符，包括中英文和数字 |
| time_range   |  ✅  | 服务时间范围：`start_time` 必填，`end_time` 选填 |
| risk_fund    |  ✅  | 订单风险金：`name`、`amount`（单位分）、`description` |
| location     |  ❌  |            使用服务地点            |
| attach       |  ❌  |      附加数据，回调时原样返回      |

### 前端调起支付分

创建订单成功后，将响应中的 `package` 传给前端，通过 `wx.openBusinessView` 拉起支付分：

```javascript
wx.openBusinessView({
  businessType: 'wxpayScoreUse',
  queryString: 'package=创建接口返回的package',
  // ...
});
```

公众号 H5（`wx.invoke`）、APP SDK 的调起方式请参考[官方文档](https://pay.weixin.qq.com/doc/v3/merchant/4012587945)。

## 查询支付分订单

`out_order_no`（商户服务订单号）与 `query_id`（查单 ID：商户调起支付分小程序确认订单页后，用户回到商户前端时带回）**二选一**，都不填写或同时填写 SDK 均会抛出异常。注意 `query_id` 不是创建应答中的 `order_id`（微信服务订单号），二者不可混用。

### 例子

```php
Pay::config($config);

$order = [
    '_action' => 'query',
    'out_order_no' => time().'',
    // 'query_id' => '查单ID（前端确认页回跳带回）', // 与 out_order_no 二选一
];

$result = Pay::wechat()->payscore($order);
```

### 订单配置参数

所有参数请参考[这里](https://pay.weixin.qq.com/doc/v3/merchant/4012587902)，查看「请求参数」一栏。

|     参数     | 必填 |                        说明                        |
|:------------:|:----:|:--------------------------------------------------:|
| out_order_no |  二选一  |                  商户服务订单号                  |
|   query_id   |  二选一  | 查单 ID（前端调起确认页后回跳带回，不是 `order_id`） |

## 取消支付分订单

只有「已创建（CREATED）」状态的订单可以取消。

### 例子

```php
Pay::config($config);

$order = [
    '_action' => 'cancel',
    'out_order_no' => time().'',
    'reason' => '用户取消订单',
];

$result = Pay::wechat()->payscore($order);
```

### 订单配置参数

所有参数请参考[这里](https://pay.weixin.qq.com/doc/v3/merchant/4012587905)，查看「请求参数」一栏。

|     参数     | 必填 |                说明                |
|:------------:|:----:|:----------------------------------:|
| out_order_no |  ✅  |          商户服务订单号（路径参数）          |
|    reason    |  ✅  | 取消原因，不超过 80 个字符，包括中英文和数字 |

## 完结支付分订单

用户使用服务完成后，商户调用完结接口确认订单金额。

### 例子

```php
Pay::config($config);

$order = [
    '_action' => 'complete',
    'out_order_no' => time().'',
    'post_payments' => [
        ['name' => '商品名', 'amount' => 100, 'count' => 1, 'description' => '商品描述'],
    ],
    'total_amount' => 100, // 订单总金额，单位：分
    'time_range' => [
        'end_time' => '20260910120000', // 完结时间
    ],
];

$result = Pay::wechat()->payscore($order);
```

### 订单配置参数

所有参数请参考[这里](https://pay.weixin.qq.com/doc/v3/merchant/4012587955)，查看「请求参数」一栏。

|     参数     | 必填 |                说明                |
|:------------:|:----:|:----------------------------------:|
| out_order_no |  ✅  |          商户服务订单号（路径参数）          |
| post_payments |  ✅  |              后付费项目              |
| post_discounts |  ❌  |              商户优惠              |
| total_amount |  ❌  |       订单总金额，单位：分       |
| time_range   |  ❌  |            完结时间            |

## 修改支付分订单金额

### 例子

```php
Pay::config($config);

$order = [
    '_action' => 'modify',
    'out_order_no' => time().'',
    'post_payments' => [
        ['name' => '商品名', 'amount' => 200, 'count' => 1, 'description' => '商品描述'],
    ],
    'total_amount' => 200, // 修改后的订单总金额，单位：分
];

$result = Pay::wechat()->payscore($order);
```

### 订单配置参数

所有参数请参考[这里](https://pay.weixin.qq.com/doc/v3/merchant/4012647427)，查看「请求参数」一栏。

|     参数     | 必填 |                说明                |
|:------------:|:----:|:----------------------------------:|
| out_order_no |  ✅  |          商户服务订单号（路径参数）          |
| post_payments |  ✅  |          修改后的后付费项目          |
| post_discounts |  ❌  |          修改后的商户优惠          |
| total_amount |  ✅  |    修改后的订单总金额，单位：分    |

## 同步支付分订单信息

### 例子

```php
Pay::config($config);

$order = [
    '_action' => 'sync',
    'out_order_no' => time().'',
    'type' => 'Order_Paid', // 同步类型，详见官方文档
    'detail' => [
        'charge' => [
            ['name' => '商品名', 'amount' => 100, 'count' => 1, 'description' => '商品描述'],
        ],
        'paid_amount' => 100, // 用户实付金额，单位：分
    ],
];

$result = Pay::wechat()->payscore($order);
```

### 订单配置参数

所有参数请参考[这里](https://pay.weixin.qq.com/doc/v3/merchant/4012587962)，查看「请求参数」一栏。

|     参数     | 必填 |                说明                |
|:------------:|:----:|:----------------------------------:|
| out_order_no |  ✅  |          商户服务订单号（路径参数）          |
|     type     |  ✅  |      同步类型，详见官方文档      |
|    detail    |  ✅  |       同步详情，内容随 `type` 而定       |

## 催收扣款

### 例子

```php
Pay::config($config);

$order = [
    '_action' => 'pay',
    'out_order_no' => time().'',
];

$result = Pay::wechat()->payscore($order);
```

### 订单配置参数

除 `out_order_no` 外无需额外业务参数，所有参数请参考[这里](https://pay.weixin.qq.com/doc/v3/merchant/4013394596)，查看「请求参数」一栏。

|     参数     | 必填 |                说明                |
|:------------:|:----:|:----------------------------------:|
| out_order_no |  ✅  |          商户服务订单号（路径参数）          |

## 商户预授权（签约）

### 例子

```php
Pay::config($config);

$order = [
    '_action' => 'permissions',
    'authorization_code' => '预授权授权码',
];

$result = Pay::wechat()->payscore($order);
// 创建成功：$result->apply_permissions_token 用于前端调起
```

### 订单配置参数

`appid`、`service_id` 由 SDK 自动注入，`notify_url` 默认取配置中的 `notify_url`。

所有参数请参考[这里](https://pay.weixin.qq.com/doc/v3/merchant/4012647349)，查看「请求参数」一栏。

|     参数     | 必填 |                说明                |
|:------------:|:----:|:----------------------------------:|
| authorization_code |  ✅  |      预授权授权码      |
| notify_url   |  ❌  |      授权成功回调地址，缺省取配置      |

### 前端调起授权

预授权接口成功后，将响应中的 `apply_permissions_token` 传给前端，通过 `wx.openBusinessView` 拉起授权页面：

```javascript
wx.openBusinessView({
  businessType: 'wxpayScoreEnable',
  queryString: 'apply_permissions_token=预授权接口返回的token',
  // ...
});
```

## 查询用户授权记录（协议号）

### 例子

```php
Pay::config($config);

$order = [
    '_action' => 'permissionsQuery',
    'authorization_code' => '预授权授权码',
];

$result = Pay::wechat()->payscore($order);
```

### 订单配置参数

所有参数请参考[这里](https://pay.weixin.qq.com/doc/v3/merchant/4012647401)，查看「请求参数」一栏。

|     参数     | 必填 |                说明                |
|:------------:|:----:|:----------------------------------:|
| authorization_code |  ✅  |          授权协议号（路径参数）          |

## 解除授权

支持两种方式：通过 `authorization_code`（授权协议号）或 `openid`（用户 openid）解除授权，二选一传入即可。

:::warning
该接口微信应答为 **HTTP 204 无包体**，SDK 返回 `Response` 对象（非 `Collection`），直接将其返回给微信即可。
:::

### 例子

```php
Pay::config($config);

$order = [
    '_action' => 'permissionsTerminate',
    'authorization_code' => '授权协议号', // 与 openid 二选一
    // 'openid' => '用户openid', // 通过 openid 解除授权时传入
    'reason' => '用户解除授权',
];

$result = Pay::wechat()->payscore($order);
// 返回 PSR-7 ResponseInterface（HTTP 204，空包体）
```

### 订单配置参数

`service_id` 由 SDK 自动注入；通过 `openid` 解除授权时 `appid` 由 SDK 自动注入。

所有参数请参考[这里](https://pay.weixin.qq.com/doc/v3/merchant/4012647410)（协议号）/ [这里](https://pay.weixin.qq.com/doc/v3/merchant/4012647413)（openid），查看「请求参数」一栏。

|     参数     | 必填 |                说明                |
|:------------:|:----:|:----------------------------------:|
| authorization_code |  二选一  |          授权协议号          |
|    openid    |  二选一  | 用户 openid（需配合 `appid` 使用） |
|    reason    |  ✅  | 解除原因，不超过 80 个字符，包括中英文和数字 |

## 接收回调

支付分回调（确认订单、支付成功、开启/解除授权等）与普通微信 V3 回调同构（JSON 信封 + `resource` 加密），使用 `Pay::wechat()->callback()` 验签并解密即可，无需传递额外 `_action`，请按明文中的 `event_type` 自行分发处理。

### 例子

```php
Pay::config($config);

$result = Pay::wechat()->callback(null);
// 返回 Collection 实例（解密后的明文），按 event_type 分发处理
```

### 回调事件类型

|        event_type        |                说明                |
|:------------------------:|:----------------------------------:|
|   PAYSCORE.USER_CONFIRM   |            用户确认订单            |
|    PAYSCORE.USER_PAID     |            用户支付成功            |
| PAYSCORE.USER_OPEN_SERVICE |      用户开启服务（授权成功）      |
| PAYSCORE.USER_CLOSE_SERVICE |        用户解除授权        |

:::warning
以上为支付分回调常见的 `event_type`，具体取值及报文格式请以官方文档为准：

- [确认订单回调通知](https://pay.weixin.qq.com/doc/v3/merchant/4012587953)
- [支付成功回调通知](https://pay.weixin.qq.com/doc/v3/merchant/4012587960)
- [开启/解除授权回调通知](https://pay.weixin.qq.com/doc/v3/merchant/4012647393)
:::

## 确认回调

|    方法名     | 参数  |   返回值    |
|:----------:|:---:|:--------:|
| success |  array  | Response |

支付分回调应答为 **HTTP 204 无包体**：

### 例子

```php
Pay::config($config);

$result = Pay::wechat()->callback(null);

// 按 event_type 处理业务逻辑...

return Pay::wechat()->success(['_action' => 'payscore']);
// 返回 HTTP 204 Response（空包体），直接返回给微信即可
```
