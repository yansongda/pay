---
feature: bestpay
status: delivered
updated: 2026-03-25
branch: feat/bestpay-provider
commits: 9fdd0f34..HEAD
---

# 翼支付（BestPay）Provider 调研与设计

对应 Issue：[yansongda/pay#1030](https://github.com/yansongda/pay/issues/1030)

一期 P0 已实现；P1（app/micropay/queryRefund）与沙箱联调见 Tasks / [S4]。

## Report

**What was built** — 按代际 B（MAPI）落地翼支付 Provider：`BestpayConfig`（PKCS12 + 平台公钥）、`Pay::bestpay()`、SDK 信封加签（TreeMap `k=v&` + SHA256withRSA）、`/mapi/sdkRequest` 网关、P0 业务（`web`/`h5`/`scan`/`query`/`refund`/`close`）、**API 响应验签 `VerifySignaturePlugin`**（嵌套 JSON 有序序列化后验签）、回调证书验签与官方成功应答 `{"resultCode":"SUCCESS","resultMsg":"OK"}`。

**Verification** — 容器 PHP 8.5：`phpunit --filter Bestpay` 19 tests OK；全量 `phpunit` OK；`phpstan -l 6 ./src` OK；`php-cs-fixer --dry-run` OK。

**Journey log** — Copilot 旧分支 MD5 不可合入；DiningOrder/ShopXO 锁定信封与加签；评审要求补响应验签后，按 Java SDK `translateMapData` 语义实现嵌套 JSON 待签串；机构字段仅写 commonParams。

## [S1] Problem

- 用户希望 yansongda/pay 支持中国电信翼支付（BestPay），Issue 指向官方开发者平台：  
  <https://render.bestpay.cn/open-developers/index.html#/documentCenterLayout/accessProcess>
- 官网仅提供 Java demo，文档站为 SPA，社区几乎无可用 PHP SDK。
- 仓库内已有远端分支 `origin/copilot/add-yipay-support`（+1458 行），但基于 **旧架构**（`Functions.php` + 数组配置），与当前 master 的 Typed Config / Trait / `Pay::PROVIDER_*` 常量体系不一致，**不能直接合入**。
- 调研已通过平台文档 API（`/gapi/telecomPortal/getApiDocument*`）拿到 **产品 1008 超级收银台 / 1006 线下聚合** 的接口目录、公共请求/响应参数、回调报文与状态机；交易接口的完整请求字段表仍缺（文档列表项 `requestParams` 为空，需登录文档中心或 Java demo）。

## [S2] Design

### [S2.1] Provider 命名与入口

| 项 | 取值 | 说明 |
|---|---|---|
| Provider key | `bestpay` | 与 `Pay::bestpay()`、`Pay::PROVIDER_BESTPAY` 一致 |
| 中文名 | 翼支付 / 中国电信翼支付 | Issue 用语 |
| 配置类 | `BestpayConfig` | 继承 `AbstractConfig` |
| 常量 | `Pay::PROVIDER_BESTPAY = 'bestpay'` | 注册进 `Config::PROVIDERS`、`Pay::$providers` |
| Service | `BestpayServiceProvider` | 继承 `AbstractServiceProvider`，`getProviderName()` 返回常量 |
| 事件名 | `'bestpay'` | `MethodCalled` / `CallbackReceived` 第一参数 |

> 不用 `yipay` 作为 key：易与「易支付」聚合混淆；分支名可保留历史，代码侧统一 `bestpay`。

### [S2.2] API 代际结论（调研核心）

公开证据表明翼支付商户开放接口至少存在 **两代**：

#### 代际 A —— 旧版商户 HTTP 接口（Copilot 分支所写）

来源：`origin/copilot/add-yipay-support` 代码本身（非官方确认）。

| 项 | 值 |
|---|---|
| Base URL | `https://api.bestpay.com.cn/` / `https://sandbox.bestpay.com.cn/` |
| 方法 | 业务接口均 POST |
| 路径 | `pay/cashierPay`、`pay/createQrPay`、`pay/queryPayOrder`、`refund/applyRefund` |
| 签名 | `signType=MD5`；过滤空值/`sign`/`signType` → `ksort` → `http_build_query` + `&key={app_key}` → `strtolower(md5(...))` |
| 配置 | `merchant_no`、`platform`（测试样例为 `HELIPAY`）、`app_key`、`notify_url`、`return_url` |
| 成功码 | `returnCode === '0000'`，`returnMsg` 为文案 |
| 回调 | JSON body，字段含 `merchantNo`/`platform`/`tradeOrder`/`totalAmount`/`sign` |

风险：GitHub 全网检索 `createQrPay` + bestpay **零结果**；Packagist 无翼支付包；该代端点与算法**无法独立交叉验证**。可能对应部分历史商户通道，也可能不完整/不准确。

#### 代际 B —— 现行开发者平台 MAPI（Issue 链接所指，目标实现）

来源：SPA 前端静态提取 + 网关探活 + **官方文档 API 无鉴权可读部分** + **GitHub 多实现交叉验证**（2026-03-25）。

##### 网关

| 项 | 值 |
|---|---|
| 业务网关 | `https://mapi.bestpay.com.cn/mapi`（GET 405，需 POST） |
| SDK 统一入口 | `POST https://mapi.bestpay.com.cn/mapi/sdkRequest?BESTPAY_MAPI_VERSION={ver}` |
| 直连路径风格 | `POST https://mapi.bestpay.com.cn/mapi{path}`（如 `/uniformReceipt/proCreateOrder`） |
| 平台网关 | `https://mapi.bestpay.com.cn/gapi/mapi-gateway`（H5 收银台/登录因子） |
| 文件服务 | `https://mapi-file.bestpay.com.cn/mapi-file` |
| 文档 API | `GET/POST /gapi/telecomPortal/getApiDocument?productCode={id}`、`?auxiliaryCode={docKey}` |

##### 调用信封（官方 Java SDK `com.bestpay.api` 反推，DiningOrder 完整复现）

```
POST /mapi/sdkRequest?BESTPAY_MAPI_VERSION=1.0.3
Content-Type: application/x-www-form-urlencoded

path=/uniformReceipt/proCreateOrder
commonParams={"institutionType":"MERCHANT","institutionCode":"3178..."}
bizContent={"merchantNo":"...","outTradeNo":"...","tradeAmt":"99",...}
sign=<base64>
```

**加签算法（已可落码）**

1. 取三元组 `path` / `commonParams` / `bizContent`，按 key **TreeMap 升序**
2. 拼接待签串：`bizContent=...&commonParams=...&path=...`（排除 `sign`；值原样，不做 URL 编码）
3. 用商户 **PKCS12 私钥**做 **`SHA256withRSA`** 签名
4. **Base64** 输出为 `sign`

**验签算法（响应）**

1. 解析响应 JSON，取出 `sign` 后移除
2. 其余字段 TreeMap 升序，同样拼 `k=v&k=v`
3. 用平台 **`.cer` 公钥**验签；DiningOrder/官方 Demo 使用 **`SHA1withRSA`** 验签  
   - 注意：请求加签是 SHA256withRSA，响应验签是 SHA1withRSA（Demo 原样）；实现时以联调为准，失败再试 SHA256withRSA

**证书**

| 文件 | 用途 |
|---|---|
| `bestpay.p12`（PKCS12，密码 + alias，样例 alias=`conname`） | 商户私钥加签 |
| `bestpay.cer`（X.509） | 平台公钥验签 |

**直连路径风格（ShopXO，PHP 可运行）**

- POST JSON 到 `https://mapi.bestpay.com.cn/mapi/uniformReceipt/{action}`
- 对 **全部业务字段** ksort → `k=v&k=v`（跳过空值、`@` 开头、`sign`）→ **`SHA256withRSA`** + Base64
- 无 `path/commonParams/bizContent` 信封；`commonParams` 信息并入字段（`institutionCode` 等）

##### `uniformReceipt` 业务字段（GitHub 实现交叉确认）

**下单 `proCreateOrder`（bizContent）**

| 字段 | 必填 | 示例/说明 |
|---|---|---|
| `merchantNo` | Y | 商户号 |
| `outTradeNo` | Y | 商户订单号 |
| `tradeAmt` | Y | 金额字符串，**分** |
| `ccy` | Y | `156`（人民币） |
| `requestDate` | Y | `yyyy-MM-dd HH:mm:ss`，有效范围约 T-1～T+1 天 |
| `tradeChannel` | Y | `APP` / 场景相关 |
| `accessCode` | Y | `CASHIER` |
| `mediumType` | Y | `WIRELESS` 等 |
| `subject` | Y | 订单标题 |
| `goodsInfo` | Y | 商品信息 |
| `operator` | Y | 常与 merchantNo 相同 |
| `notifyUrl` | Y | 异步通知 |
| `storeCode`/`storeName` | O | 门店 |
| `riskControlInfo` | O | JSON 字符串，见官方风控参数 |

**查询 `tradeQuery`**：`outTradeNo`、`merchantNo`、`tradeDate`  
**退款 `tradeRefund`**：`merchantNo`、`outTradeNo`、`outRequestNo`、`originalTradeDate`、`refundAmt`、`requestDate`、`operator`、`tradeChannel`、`ccy`、`accessCode`、`remark`

**下单成功响应（Demo 实录）**

```json
{
  "success": true,
  "errorCode": null,
  "errorMsg": null,
  "result": {
    "merchantNo": "3178033925245778",
    "outTradeNo": "510519614129687",
    "tradeNo": "20210910100000210002110248683601",
    "tradeStatus": "WAITFORPAY",
    "tradeprodNo": "2021091017TPPIOP1110001459598843"
  },
  "sign": "O8qDRI8SFBip0Ek..."
}
```

`tradeStatus` 见官方附录：`SUCCESS` / `FAIL` / `NOTPAY` / `CLOSE`；下单后常为 `WAITFORPAY`。

##### 公共请求参数（官方 1008 文档）

| 字段 | 类型 | 必填 | 说明 |
|---|---|---|---|
| `signType` | String | M | CA 证书固定 `S002`；国密 `S008` |
| `signMerchantNo` | String | C | 加签 CA 证书对应商户号 |
| `institutionCode` | String | C | 与 `signMerchantNo` 一致 |
| `sign` | String | M | 签名值 |

> SDK 信封风格里机构信息在 `commonParams`；直连风格里可能平铺。实现插件时统一抽 `institutionType/institutionCode`。

##### 公共响应参数

| 字段 | 类型 | 说明 |
|---|---|---|
| `success` | Boolean | 通讯处理结果 |
| `result` | Object | 业务载荷；失败时常为 null |
| `errorCode` | String(10) | 如 `API100001` 签名失败、`API500009` 信封解密失败 |
| `errorMsg` | String(1024) | 错误描述 |
| `sign` | String | 返回结果签名 |

##### 产品 1008「超级收银台」接口目录（官方文档，已核验）

| API 路径 | 名称 | 一期映射 |
|---|---|---|
| `/pay/tradeCreate` | 聚合统一下单 | P0 支付（web/h5/app 共用，场景参数区分） |
| `/integrate/orderQuery` | 订单查询 | P0 `query()` |
| `/integrate/refund` | 订单退款 | P0 `refund()` |
| `/integrate/refundQuery` | 退款查询 | P1 |
| `/pay/closeOrder` | 订单关闭 | P0 `close()` |
| `/integrate/splitDelayedConfirm` | 担保交易确认/取消 | 二期 |
| `/integrate/splitDelayedQuery` | 担保交易确认/取消查询 | 二期 |
| 文档：`aggregatePayOrRefundNotify` | 支付/回调 | P0 `callback()` |
| 文档：`aggregateGetOpenid` | 获取用户 openid | P1 |
| 文档：`PublicParameters1007` / `symbolDefinition` / `aggregateRemark*` | 公共参数、枚举、附录 | 实现参考 |

> `tradeCreate` 与 `uniformReceipt/proCreateOrder` 是 **同一 MAPI 上不同产品路径**：前者对应聚合超级收银台（1008），后者对应 APP 子应用统一收单。一期插件用 `path` 可配置，业务字段以官方 1008 为准；GitHub 的 `uniformReceipt` 字段作加签/信封实现对照。

##### 产品 1006「线下聚合」接口目录（官方，已核验）

| API 路径 | 名称 | 一期映射 |
|---|---|---|
| `/aggregate/aggregatepay/offline/c2b/payOrder` | 聚合收款码下单 | P0 `scan()` |
| `/aggregate/aggregatepay/offline/b2c/pay` | 聚合付款码下单 | P1 `micropay()` |
| `/aggregate/aggregatepay/offline/refund` | 退款 | 按签约产品 |
| `/aggregate/aggregatepay/tradeQuery` | 下单结果查询 | 按产品分流 |
| `/o2o/aggregatePay/service/closeTrade` | 关单 | 可选 |
| `/o2o/aggregatePay/app/offlineCancel` | 撤销 | P1 |

##### 回调契约（官方 `aggregatePayOrRefundNotify`）

- 方向：翼支付 POST JSON 到商户 `notify_url`
- 关键字段：`institutionCode`、`merchantNo`、`outTradeNo`、`outRefundNo`（仅 REFUND）、`notifyType`（`PAY`|`REFUND`）、`totalAmt`/`payAmt`/`refundAmt`（单位**分**）、`tradeStatus`、`tradeNo`、`sign`、`payTool`、`payFinishedDate`/`refundFinishedDate`
- **必须以 `tradeStatus` 判断交易结果**，`SUCCESS`/`FAIL`/`NOTPAY`/`CLOSE`
- 商户成功应答：`{"resultCode":"SUCCESS","resultMsg":"OK"}`
- 失败应答：`{"resultCode":"FAILED","resultMsg":"FAILED"}`
- 重试：最多 2 次，间隔约 5 分钟；需处理重复通知
- 出口 IP：测试 `116.228.151.2`；生产 `58.213.97.1`、`58.213.98.128`、`58.213.98.232-239`、`117.68.124.1-8`

##### 交易渠道枚举

`BESTPAY`、`ALIPAY`、`WECHAT`、`QUICKPASS`、`CTDIGICCY`、`WEBCASHIER`、`MOBILECASHIER`、`APPLETCASHIER`、`AGGCODE`、`MIXCODE`、`B2C`/`B2B`、`JINGDONGPAY`

##### 其他代际（仅对照）

| 代际 | 特征 | 代表 |
|---|---|---|
| C 旧 MD5 MAC | `MERCHANTID`/`ORDERNO`/`KEY` → `mac=MD5(...)` 大写，表单 GET/POST | thlws/payment-bestpay |
| A Copilot 臆测 | `api.bestpay.com.cn` + `returnCode=0000` + app_key MD5 | copilot/add-yipay-support |

**结论**：一期实现 **代际 B 的 SDK 信封 + 证书加签**；路径优先覆盖官方 1008/1006 目录，用 `uniformReceipt` Demo 校准签名与信封。

### [S2.3] 架构映射（对齐当前 master）

按 `.agents/skills/dev-guide/SKILL.md` 六阶段，与最近合入的 Airwallex/Jsb 保持一致：

```
src/
├── Config/BestpayConfig.php
├── Provider/Bestpay.php
├── Service/BestpayServiceProvider.php
├── Traits/BestpayTrait.php          # getBestpayUrl / verifyBestpaySign / 加签辅助
├── Plugin/Bestpay/V1/
│   ├── StartPlugin.php              # 注入 merchantNo/institutionCode/signType 等公共字段
│   ├── AddPayloadSignPlugin.php     # CA 证书加签（S002；S008 视 [S4]）
│   ├── AddRadarPlugin.php           # POST https://mapi.bestpay.com.cn/mapi + JSON body
│   ├── ResponsePlugin.php           # success + errorCode/errorMsg + 响应验签
│   ├── CallbackPlugin.php           # 必须验签；tradeStatus 判定
│   └── Pay/
│       ├── Web/PayPlugin.php        # tradeCreate + WEBCASHIER
│       ├── H5/PayPlugin.php         # tradeCreate + MOBILECASHIER
│       ├── App/PayPlugin.php        # tradeCreate APP（P1）
│       ├── Scan/PayPlugin.php       # 1006 c2b payOrder
│       ├── MicropayPlugin.php       # 1006 b2c pay（P1）
│       ├── QueryPlugin.php          # /integrate/orderQuery
│       ├── RefundPlugin.php         # /integrate/refund
│       ├── ClosePlugin.php          # /pay/closeOrder
│       └── QueryRefundPlugin.php    # P1
└── Shortcut/Bestpay/
    ├── WebShortcut.php
    ├── H5Shortcut.php
    ├── AppShortcut.php              # P1
    ├── ScanShortcut.php
    ├── MicropayShortcut.php         # P1
    ├── QueryShortcut.php
    ├── RefundShortcut.php
    ├── CloseShortcut.php
    └── QueryRefundShortcut.php      # P1
```

注册触点（实现时同步改）：

- `src/Pay.php`：`PROVIDER_BESTPAY`、`@method static Bestpay bestpay()`、`$providers`
- `src/Config.php`：`PROVIDERS` 与 `match` 映射
- `src/Exception/Exception.php`：`CONFIG_BESTPAY_INVALID`、`PARAMS_BESTPAY_*`
- `README.md` 支持列表 + `web/docs/v3/**` + `web/.vitepress/sidebar/v3.js`
- 测试：`tests/Provider/BestpayTest.php`、`tests/Plugin/Bestpay/**`（Mock HTTP，禁止真实请求）

插件管道默认：

```
StartPlugin
  → 业务插件 (Web|H5|App|Scan|Micropay|Query|Refund|Close|QueryRefund)
  → AddPayloadSignPlugin
  → AddRadarPlugin
  → ResponsePlugin
  → ParserPlugin
```

`ProviderInterface::cancel()`：官方无对等取消；1006 `offlineCancel` 映射为 `micropay()` 族 shortcut，不占用 `cancel()`，避免与微信/支付宝语义混淆。

### [S2.4] 配置契约（证书加签，可落码）

| 配置键 | 必填 | 说明 |
|---|---|---|
| `merchant_no` | 是 | 商户号 |
| `institution_code` | 是 | 机构号，`commonParams.institutionCode` |
| `institution_type` | 否 | 默认 `MERCHANT` |
| `mch_secret_cert_path` | 是 | 商户 PKCS12（`bestpay.p12`），走 `CertManager::getPrivateCert()` |
| `mch_secret_cert_password` | 是 | P12 密码 |
| `mch_secret_cert_alias` | 否 | 默认 `conname`（官方 Demo 样例） |
| `bestpay_public_cert_path` | 是 | 平台 `.cer` 公钥（响应验签） |
| `api_version` | 否 | 默认 `1.0.3`（`BESTPAY_MAPI_VERSION`） |
| `sign_type` | 否 | 默认 `S002`；国密 `S008` |
| `notify_url` | 否（可 per-order） | 支付/退款异步通知 |
| `return_url` | 否 | 同步跳转 |
| `mode` | 否 | `MODE_NORMAL` / `MODE_SANDBOX`；不支持 `MODE_SERVICE` |

校验：`supportedModes(): [MODE_NORMAL, MODE_SANDBOX]`；`validateRequired()` 缺商户号/私钥/密码/平台公钥抛 `CONFIG_BESTPAY_INVALID`。

> 金额统一 **分**；SDK 不做自动换算。

### [S2.5] 一期能力范围（用户已确认：更全一期，映射官方 1008/1006）

| Shortcut | 对外方法 | 官方接口 | 优先级 |
|---|---|---|---|
| Web | `web()` | 1008 `/pay/tradeCreate` + `WEBCASHIER` | P0 |
| H5 | `h5()` | 1008 `/pay/tradeCreate` + `MOBILECASHIER` | P0 |
| App | `app()` | 1008 `/pay/tradeCreate`（APP 场景，字段待文档中心确认） | P1 |
| Scan | `scan()` | 1006 `/aggregate/aggregatepay/offline/c2b/payOrder` | P0 |
| Micropay | `micropay()` | 1006 `/aggregate/aggregatepay/offline/b2c/pay` | P1 |
| Query | `query()` | 1008 `/integrate/orderQuery`（1006 订单按产品分流） | P0 |
| Refund | `refund()` | 1008 `/integrate/refund` | P0 |
| Close | `close()` | 1008 `/pay/closeOrder` | P0 |
| Callback | `callback()` + `success()` | `aggregatePayOrRefundNotify` | P0 |
| QueryRefund | `queryRefund()` | 1008 `/integrate/refundQuery` | P1 |
| cancel | — | 官方无对等 cancel；1006 有 `offlineCancel`，映射为 `micropayCancel()` 而非 Provider::cancel | P1 |

### [S2.6] 安全与回调

1. **回调必须验签**，禁止信任未验签 body（AGENTS.md 硬约束）。
2. 请求加签：`path`/`commonParams`/`bizContent` TreeMap → `k=v&` → **SHA256withRSA**（PKCS12 私钥）→ Base64。
3. 响应/回调验签：去掉 `sign` 后其余字段 TreeMap → `k=v&` → 平台 `.cer` 公钥验签（Demo 为 **SHA1withRSA**，联调失败则试 SHA256withRSA）。
4. 业务判定以 `tradeStatus` 为准（`SUCCESS` 才成功）；同时校验 `outTradeNo`/金额与商户侧一致。
5. `success()` 返回官方应答：`{"resultCode":"SUCCESS","resultMsg":"OK"}`。
6. 日志/异常消息中文；金额单位为分，文档与 PHPDoc 标明。

### [S2.7] 测试边界

- 加签/验签：固定证书与明文 → 期望 sign（覆盖 `signType` S002 路径）。
- 回调：合法签名通过；篡改字段失败；缺 `sign` 失败；`tradeStatus` 非 `SUCCESS` 不得当成功。
- `success()` body 精确等于 `{"resultCode":"SUCCESS","resultMsg":"OK"}`。
- 响应插件：HTTP 非 2xx / `success=false` / 业务失败 → `InvalidResponseException`。
- Provider：`mergeCommonPlugins` 顺序、`close`/`cancel` 行为、shortcut 分发。
- 全部 Mock `HttpClientInterface`；不依赖外网。

## [S3] Out of Scope

- 本轮不编写 `src/` 业务实现、不提交 feature 代码。
- 不实现服务商/机构模式（翼支付一期商户直连）。
- 不对接翼支付 C 端 App SDK、小程序插件（文档有 `APPLETCASHIER`/`aggregateGetOpenid`，二期再评估）。
- 不迁移/直接合入 `origin/copilot/add-yipay-support`（架构与算法均过时）。
- 不承诺代际 A 端点可用。
- 不实现：担保交易延时分账、对账文件、商户进件（1001）、代扣（1009）、企业账户（1012）、灵活用工（1017）等非收单主路径产品。

## [S4] 阻塞项 / 待确认事实

已关闭（官方文档 API + GitHub 实现交叉验证，2026-03-25）：

- [x] 目标代际：**只做 B（MAPI）**，产品线 1008 + 1006
- [x] 接口目录：见 [S2.2]
- [x] 公共请求/响应字段与 `signType=S002|S008`
- [x] 回调报文、成功应答、以 `tradeStatus` 为准、金额单位为分
- [x] **MAPI SDK 调用信封**：`/mapi/sdkRequest?BESTPAY_MAPI_VERSION=` + `path/commonParams/bizContent/sign`
- [x] **加签算法**：TreeMap `k=v&` → SHA256withRSA（PKCS12）→ Base64
- [x] **响应验签**：平台 `.cer` 公钥，Demo 为 SHA1withRSA
- [x] `uniformReceipt/proCreateOrder|tradeQuery|tradeRefund` 完整字段（ShopXO + DiningOrder）
- [x] 官方存在 `/pay/closeOrder`，一期实现 `close()`

仍待关闭（**不阻塞 T2 骨架与签名单测**，阻塞联调）：

1. **1008 `tradeCreate` 与 1006 下单的精确 bizContent 字段表**（文档 `requestParams` 仍空；可先用 `uniformReceipt` 字段 + 官方回调字段推导，联调时校准）
2. **响应验签哈希**：SHA1withRSA vs SHA256withRSA，以实际证书联调为准（实现可配置/双试）
3. **沙箱 base URL** 与测试商户申请流程
4. **S008 国密**是否一期支持（默认只做 S002）

## Tasks

实现阶段任务：

- [x] T1: 骨架注册 — BestpayConfig + Provider + ServiceProvider + Pay/Config/Exception 常量 — acceptance: `Pay::bestpay()` 可实例化；缺配置抛 `CONFIG_BESTPAY_INVALID` (covers: S2.1, S2.4)
- [x] T2: 加签/验签与网络插件 — StartPlugin / AddPayloadSignPlugin / AddRadarPlugin / ResponsePlugin + BestpayTrait（TreeMap 串、SHA256withRSA、sdkRequest URL）— acceptance: 固定样例签名单测通过（可用测试证书）；管道可组装 (covers: S2.2, S2.3, S2.6; depends: T1)
- [x] T3: P0 业务插件与 Shortcut（web/h5/scan/query/refund/close）— acceptance: path 与 [S2.2] 一致，Mock HTTP 返回 Collection (covers: S2.3, S2.5; depends: T2)
- [x] T4: 回调验签 + `success()` — acceptance: 合法/非法签名用例全绿；应答 body 精确匹配 (covers: S2.6; depends: T2)
- [ ] T5: P1（app/micropay/queryRefund/micropayCancel）— acceptance: 有字段依据才合入，否则文档声明不支持 (covers: S2.5; depends: T3)
- [x] T6: `composer cs-fix && composer analyse && composer test` — acceptance: 三项全绿 (covers: S2.7; depends: T3, T4)
- [x] T7: 文档 — README、quick-start、bestpay 文档、sidebar；金额「分」与证书配置醒目 — acceptance: 示例与 BestpayConfig 一致 (covers: S2.1, S2.4; depends: T1, T3, T4)
- [ ] T8: 联调校准（需商户沙箱）— tradeCreate/1006 字段与验签哈希 — acceptance: 真实沙箱下单/查询/回调通过 (covers: S4; depends: T6)

## 调研附录

### A. 官方文档 API（2026-03-25）

```
POST https://mapi.bestpay.com.cn/gapi/mapi-gateway/auth/getPublicKey
GET  .../telecomPortal/getApiDocument?productCode=1008
GET  .../telecomPortal/getApiDocument?productCode=1006
GET  .../telecomPortal/getApiDocument?auxiliaryCode=PublicParameters1007
GET  .../telecomPortal/getApiDocument?auxiliaryCode=aggregatePayOrRefundNotify
GET  .../telecomPortal/getApiDocument?auxiliaryCode=aggregateRemark
GET  https://ctcdn.bestpay.cn/html/images/collectpay/product.json
```

产品码：`1001` 进件、`1006` 线下聚合、`1008` 超级收银台、`1009` 代扣、`1611` 统一超收、`1016` 分账。

### B. GitHub 实现对照（本轮「全力搜索」核心产出）

| 仓库 | 语言 | 对本次调研的价值 |
|---|---|---|
| [gongfuxiang/shopxo](https://github.com/gongfuxiang/shopxo) `extend/payment/Bestpay.php` | PHP | **可运行**：P12+SHA256withRSA、`uniformReceipt` 下单/查询/退款全字段、直连路径风格 |
| [Belos10/DiningOrder](https://github.com/Belos10/DiningOrder) `utils/payUtil/*` | Java | **官方 SDK 反推**：`sdkRequest` 信封、AssembleUtil 排序串、SignEncryptUtil、响应 SHA1withRSA 验签、成功/失败响应样例 |
| [thlws/payment-bestpay](https://github.com/thlws/payment-bestpay) | Java | 旧 MD5 MAC 代际对照 + 官方 PDF（`docs/电信翼支付.pdf`，旧通道） |
| [thlws/payment-thl](https://github.com/thlws/payment-thl) | Java | 三合一维护分支 |
| [mrjunfa/XiaoYiGuanJia](https://github.com/mrjunfa/XiaoYiGuanJia) `wapPay.html` | JS | H5 侧 AES-128-CBC + RSA 公钥 + MD5（`encyType=C005`）——收银台前端通道，商户服务端可不依赖 |

**DiningOrder 待签串示例**（加签输入）：

```
bizContent={"accessCode":"CASHIER",...}&commonParams={"institutionType":"MERCHANT","institutionCode":"..."}&path=/uniformReceipt/proCreateOrder
```

### C. Copilot 分支（仅历史对照，**不要照抄**）

| 差异点 | Copilot | 代际 B |
|---|---|---|
| 签名 | MD5 + app_key | SHA256withRSA + P12 |
| 网关 | api.bestpay.com.cn | mapi.bestpay.com.cn/mapi/sdkRequest |
| 成功码 | returnCode=0000 | success + tradeStatus |
| 回调应答 | returnCode/Msg | resultCode/resultMsg |

### D. 对齐检查清单（实现 PR 自检）

- [ ] 使用 `Pay::PROVIDER_BESTPAY`
- [ ] 加签串按 TreeMap `k=v&`，排除 `sign`
- [ ] 回调/响应验签失败路径有测试；成功应答精确匹配
- [ ] 金额单位「分」写明
- [ ] `declare(strict_types=1)` + use 导入 + 中文日志
- [ ] 不写入代际 A / Copilot MD5 路径到用户文档

