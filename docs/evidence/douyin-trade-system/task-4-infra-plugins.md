# 2026-09-05 12:29:47

## 处理事项

Task 4：ObtainClientTokenPlugin + AddRadarPlugin + ResponsePlugin + DouyinTrait::getDouyinClientToken + 测试。

改动文件：
- `src/Traits/DouyinTrait.php`：新增 `getDouyinClientToken(array $params): string`（缓存判断 → 最小参数集子调用 `[StartPlugin, GetClientTokenPlugin, AddRadarPlugin, GetClientTokenResponsePlugin, ParserPlugin]` → `data.access_token`/`data.expires_in` 解析 → `setAccessToken`/`setAccessTokenExpiry(time()+expires_in-60)` 写回），补 use 导入；模式对齐 `PaypalTrait::getPaypalAccessToken()`。
- `src/Plugin/Douyin/V1/ObtainClientTokenPlugin.php`（新建）：照抄 Paypal 同名插件结构，`_access_token` 优先、否则走 trait 子调用，`mergePayload(['_access_token' => $token])`。
- `src/Plugin/Douyin/V1/AddRadarPlugin.php`（新建）：一律 POST；`_body` 优先、无 `_body` 回退 `filter_params($payload)` 的 `json_encode(..., JSON_UNESCAPED_UNICODE)`、filter 后为空则 `''`；headers 为 `Content-Type: application/json` + `User-Agent: yansongda/pay-v3` + `_access_token` 非空时加 `access-token`；URL 走 `self::getDouyinUrl($config, $payload)`。
- `src/Plugin/Douyin/V1/ResponsePlugin.php`（新建）：HTTP 非 2xx 抛 `InvalidResponseException(Exception::RESPONSE_CODE_WRONG)`；destination 为 Collection 且 `err_no !== 0` 抛 `RESPONSE_BUSINESS_CODE_WRONG`，message 含 `err_msg ?? err_tips`。
- `tests/Plugin/Douyin/V1/{ObtainClientTokenPluginTest,AddRadarPluginTest,ResponsePluginTest}.php`（新建，共 9 用例）：ObtainClientToken 3 用例（外部 token 不触发子调用 + Mockery `never()` + `Mockery::close()` 验证 / mock HttpClientInterface 真实子调用断言请求体仅 grant_type/client_key/client_secret 三字段且 token 写回缓存 / `_return_rocket=true`+业务字段回归 #1196）；AddRadar 3 用例（无 token 头+filter 回退+URL 拼接 / access-token 头+_body 优先 / 仅 `_` 键空串）；Response 3 用例（err_no=0 通过 / err_no=10000 抛业务异常 / HTTP 500 抛状态码异常）。

## 命令与真实输出

```bash
docker run --rm -v "$(pwd)":/app -w /app registry.cn-shenzhen.aliyuncs.com/yansongda/php:cli-8.5-alpine sh -c "COMPOSER_ALLOW_SUPERUSER=1 composer cs-fix && COMPOSER_ALLOW_SUPERUSER=1 composer analyse && COMPOSER_ALLOW_SUPERUSER=1 vendor/bin/phpunit tests/Plugin/Douyin/V1/"
```
- cs-fix：`Found 0 of 475 files that can be fixed`
- analyse：`[OK] No errors`
- 目录测试：`OK (15 tests, 44 assertions)`（Task 3 的 6 + 本次新增 9）

```bash
docker run --rm -v "$(pwd)":/app -w /app registry.cn-shenzhen.aliyuncs.com/yansongda/php:cli-8.5-alpine sh -c "COMPOSER_ALLOW_SUPERUSER=1 composer test"
```
- `OK (1385 tests, 3364 assertions)`（基线 1376/3336 + 9 tests/28 assertions，差额自洽）

## 验收结果

- 三绿通过（cs-fix 0 fixable / analyse OK / test OK）
- `vendor/bin/phpunit tests/Plugin/Douyin/V1/` 全绿（15 tests，含 Task 3 的 6 个用例）
- git add 范围：本次 7 个代码/测试文件 + `git add -f docs/evidence/douyin-trade-system/task-4-infra-plugins.md`；工作区中 task-1/2/3 evidence 的已有改动与 `docs/alipay-v3.md`、`docs/douyin-trade-system.md` 为他人/他任务产物，未纳入本次 commit

## 偏差

1. `$token = $result->get('data.access_token')` 实现为 `$result->get('data.access_token', '')`（补空串默认值）：todo 同时要求「模式对齐 PaypalTrait::getPaypalAccessToken()」且方法返回类型为 `string`，无默认值时缺失键会返回 null 与返回类型冲突；机械性修正。
2. ObtainClientTokenPluginTest 用例 ①额外加了 `sendRequest()->never()` mock + `Mockery::close()` 验证：todo 要求断言「不触发子调用」，仓库 tests/EdgeCase 已有 `Mockery::close()` 先例；比 PayPal 原版裸测试更严格，属对 todo 的直接落实。
3. ResponsePlugin 业务异常 message 采用 `err_no=<值>, err_msg=<err_msg ?? err_tips>` 格式（老 master 版本只拼 err_tips），按 todo 指定表达式实现。
