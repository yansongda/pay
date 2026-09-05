# 2026-09-05 13:07:05

## 处理事项

Task 10：`src/Provider/Douyin.php` 接线新交易系统回调入口与公共插件链 + `tests/Provider/DouyinTest.php` 测试重建。

### src/Provider/Douyin.php

- 恢复/新增 use 导入：`GuzzleHttp\Psr7\ServerRequest`、`Yansongda\Pay\Event\CallbackReceived`、`Yansongda\Pay\Plugin\Douyin\V1\Pay\CallbackPlugin`、`Yansongda\Pay\Plugin\Douyin\V1\Refund\CallbackPlugin as RefundCallbackPlugin`（别名避免类名冲突）、`Yansongda\Pay\Plugin\Douyin\V1\Refund\PreRefundCallbackPlugin`。
- 重写 `callback()`，保持接口宽签名 `callback(array|ServerRequestInterface|null $contents = null, ?array $params = null): Collection|Rocket`：
  - 入参归一：ServerRequestInterface 直接用；null → `ServerRequest::fromGlobals()`；array → 抛 `InvalidParamsException(Exception::PARAMS_CALLBACK_REQUEST_INVALID, '参数异常: 抖音新交易系统回调需要携带回调头信息以完成验签，仅支持 ServerRequestInterface 入参')`；
  - `Event::dispatch(new CallbackReceived(Pay::PROVIDER_DOUYIN, clone $request, $params, null))`（形态对齐 Wechat Provider）；
  - `return $this->pay([CallbackPlugin::class], array_merge($params ?? [], ['_request' => $request]))`。
- 新增窄签名方法 `refundCallback(ServerRequestInterface $request, ?array $params = null)` 与 `preRefundCallback(ServerRequestInterface $request, ?array $params = null)`：同构（Event 派发 + `$this->pay([...], array_merge($params ?? [], ['_request' => $request]))`），分别分发 `RefundCallbackPlugin` / `PreRefundCallbackPlugin`，docblock 补 `@param null|array<string, mixed> $params`（phpstan level 6 要求）。
- 未实现 `mergeCommonPlugins()`（#1173 已删，遵守）；`success()`/`pay/query/cancel/close/refund/__call` 原样；类 docblock `@method mini` 原样。

### tests/Provider/DouyinTest.php（重建，12 tests / 52 assertions）

- `testShortcutNotFound`：`Pay::douyin()->foo()` → Artful `InvalidParamsException` `PARAMS_SHORTCUT_INVALID`。
- `testCallMini`：`Pay::douyin()->mini($order)` 端到端（无 HTTP）→ 返回 Collection 且 `array_keys` 恰为 `['data', 'byteAuthorization']`，`data` 与入参字段全等；再从 `byteAuthorization` 正则反解 `nonce_str/timestamp/signature`，用 `tests/Cert/douyinAppPublicKey.pem` 对待签串 `POST\n/requestOrder\n{ts}\n{nonce}\n{data}\n` 复算 `openssl_verify === 1`（mini 带病发布兜底）。
- `testCancel` / `testClose`：抛 `InvalidParamsException` `PARAMS_METHOD_NOT_SUPPORTED`（与现行实现一致）。
- `testQuery` / `testRefund`：mock `HttpClientInterface`（`Mockery::mock(Client::class)`，`sendRequest` 顺序返回 token 响应 + 业务响应，`twice()` + `Mockery::close()` 验证次数），`_return_rocket => true` 走真实插件链（含 ObtainClientToken 子调用）；断言 destination 业务字段 + payload `array_keys` 精确全等（query：`['out_order_no', '_return_rocket', '_access_token', '_method', '_url', '_body']`；refund：`[...9键..., '_method', '_url', 'notify_url', '_body']`，证明无 app_id 注入且 notify_url 走配置缺省注入）。
- 三回调全链路：`testCallback`（type=payment）/`testRefundCallback`（type=refund）/`testPreRefundCallback`（type=pre_create_refund），platform 私钥现签 `Byte-Signature`（待签串 `{ts}\n{nonce}\n{body}\n`），断言验签→解析→返回 msg 业务字段 Collection。
- 负向：`testCallbackWithArrayContents`（array 入参抛 9221 `PARAMS_CALLBACK_REQUEST_INVALID`）；`testCallbackTamperedSignature`（篡改 `Byte-Signature` 抛 `InvalidSignException` `SIGN_ERROR`）。
- `testSuccess`：body 逐字 `{"err_no":0,"err_tips":"success"}`。

## 命令与真实输出

```
docker run --rm -v "$(pwd)":/app -w /app registry.cn-shenzhen.aliyuncs.com/yansongda/php:cli-8.5-alpine \
  sh -c "COMPOSER_ALLOW_SUPERUSER=1 composer cs-fix && COMPOSER_ALLOW_SUPERUSER=1 composer analyse && COMPOSER_ALLOW_SUPERUSER=1 composer test"

# cs-fix:
#   Found 0 of 487 files that can be fixed in 6.178 seconds, 82.00 MB memory used
# analyse:
#    [OK] No errors
# test:
#   OK (1438 tests, 3535 assertions)

docker run --rm -v "$(pwd)":/app -w /app registry.cn-shenzhen.aliyuncs.com/yansongda/php:cli-8.5-alpine \
  sh -c "COMPOSER_ALLOW_SUPERUSER=1 vendor/bin/phpunit tests/Provider/DouyinTest.php"
# OK (12 tests, 52 assertions)
```

## 验收结果

1. 三绿（全量）：通过 — cs-fix 0 fixable / analyse [OK] No errors / `OK (1438 tests, 3535 assertions)`。
2. 单文件：通过 — `OK (12 tests, 52 assertions)`。
3. 测试数差额心算：基线 1426 tests/3483 assertions → 新增 DouyinTest 12 tests/52 assertions → 1438/3535，与全量实测一致。

## 偏差

- 无设计性偏差。实施中两处机械性修正：
  1. 初版 payload 键断言漏了链尾 `AddPayloadBodyPlugin` 注入的 `_body` 键，按实测补上；
  2. refund payload 中 `notify_url` 实际位于 `_method/_url` 之后（RefundPlugin merge 顺序），按实测调整断言键序。
- 首轮 cs-fix/analyse 通过后，phpstan 曾报 2 个 `missingType.iterableValue`（新方法 `$params`），按接口 `ProviderInterface::callback` 同款风格补 `@param null|array<string, mixed> $params` 后三绿，非设计变更。

## Git

- 提交文件（明确列出，未用 `git add -A`）：
  - `src/Provider/Douyin.php`
  - `tests/Provider/DouyinTest.php`
  - `git add -f docs/evidence/douyin-trade-system/task-10-provider.md`（本文件）
- commit message：`feat(douyin): Provider 接入新交易系统回调入口与公共插件链`；特性分支 `feature/douyin-trade-system`，未 push、未动 master。

# 2026-09-05 13:20:00 (main agent 亲自验证)

- 单文件：`vendor/bin/phpunit tests/Provider/DouyinTest.php` → `OK (12 tests, 52 assertions)`。
- 全量三绿（容器复跑）：cs-fix `Found 0 of 487` / analyse `[OK] No errors` / test `OK (1438 tests, 3535 assertions)`（1426+12=1438 自洽）。
- diff 内容级审查：callback() 保持接口宽签名（ProviderInterface.php:45 约束）✅；入参归一三分支（ServerRequest 直用/null→fromGlobals/array→9221）✅；Event::dispatch(CallbackReceived(PROVIDER_DOUYIN, clone $request, $params, null)) 对齐 Wechat 先例形态 ✅；分发 Pay\CallbackPlugin + `_request` 传参 ✅；refundCallback/preRefundCallback 窄签名新方法 + RefundCallbackPlugin 别名导入 + PreRefundCallbackPlugin 分发 ✅；未引入 mergeCommonPlugins ✅；success()/其余方法未动 ✅。
- worker 偏差核实：payload 断言补 _body 键、notify_url 键序实测调整、@param iterable 注解补齐——均机械性。
- 结论：Task 10 通过，勾选 [x]。
