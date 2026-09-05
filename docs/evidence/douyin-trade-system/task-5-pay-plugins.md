# 2026-09-05 12:41:45 (worker-low - Task 5 Pay/SignPlugin + QueryPlugin + QueryCpsPlugin)

## 处理事项

Todo 5 全量：创建 `src/Plugin/Douyin/V1/Pay/SignPlugin.php`（JSAPI 下单签名，NoHttpRequestDirection + post-$next destination 模式）、`QueryPlugin.php`（order_query）、`QueryCpsPlugin.php`（query_cps），及对应三个测试文件。共 6 个文件，未触碰邻居（Refund/、CallbackPlugin*、Task 1-4 文件均未改动）。

## 实现要点

- SignPlugin：`filter_params($payload)->all()` 剔 `_` 键（filter_params 同时剔 null 值键）；空业务字段抛 `InvalidParamsException(Exception::PARAMS_NECESSARY_PARAMS_MISSING, ...)`；`$data = json_encode($fields, JSON_UNESCAPED_UNICODE)`；`self::getDouyinTradeSign($config, 'POST', '/requestOrder', $data)`；`setDirection(NoHttpRequestDirection::class)` 在 `$next` 前、`setDestination(new Collection(['data' => ..., 'byteAuthorization' => ...]))` 在 `$next` 后（对齐 Wechat/Virtual/PayPlugin:88-94 模式）。
- QueryPlugin/QueryCpsPlugin：仅 `mergePayload(['_method' => 'POST', '_url' => ...])`，透传业务字段，无任何注入。
- 测试：SignPluginTest 从输出 byteAuthorization 正则反解 nonce_str/timestamp/signature 后用 `tests/Cert/douyinAppPublicKey.pem` 复算验签（`openssl_verify === 1`）；Query/QueryCps 用 `assertSame` 全量 keys 断言（含无 app_id 注入）。

## 命令与真实输出

1. 文件级白名单测试（Docker，宿主机仓库根目录）：
   ```
   docker run --rm -v "$(pwd)":/app -w /app registry.cn-shenzhen.aliyuncs.com/yansongda/php:cli-8.5-alpine sh -c "COMPOSER_ALLOW_SUPERUSER=1 vendor/bin/phpunit tests/Plugin/Douyin/V1/Pay/SignPluginTest.php tests/Plugin/Douyin/V1/Pay/QueryPluginTest.php tests/Plugin/Douyin/V1/Pay/QueryCpsPluginTest.php"
   ```
   ```
   PHPUnit 11.5.56 by Sebastian Bergmann and contributors.
   Runtime:       PHP 8.5.10
   Configuration: /app/phpunit.xml
   .........                                                           9 / 9 (100%)
   Time: 00:00.182, Memory: 6.00 MB
   OK (9 tests, 30 assertions)
   ```
2. phpstan 单目录自检（仅我的 3 个 src 文件，未跑全量）：
   ```
   COMPOSER_ALLOW_SUPERUSER=1 vendor/bin/phpstan analyse --memory-limit 300M -l 6 src/Plugin/Douyin/V1/Pay/
   [OK] No errors
   ```
3. php-cs-fixer 单目录自检（仅 src/Plugin/Douyin/V1/Pay，未跑全量）：`Found 0 of 4 files that can be fixed`。
4. `git status --short`：除本任务 6 个文件与邻居在途（`src/Plugin/Douyin/V1/Refund/`、`tests/Plugin/Douyin/V1/Pay/CallbackPluginTest.php`、docs 若干）外，无其他 src/tests 改动。

## 验收结果

- 验收标准 1：三个测试文件全绿 `OK (9 tests, 30 assertions)` ✅
- 验收标准 2：未触碰邻居文件，commit 仅白名单 6 文件 + evidence ✅

## 偏差

1. **机械性修正**：Todo 5a 写 `setDirection(\Yansongda\Artful\Plugin\NoHttpRequestDirection::class)`，该类真实 FQCN 为 `Yansongda\Artful\Direction\NoHttpRequestDirection`（Wechat/Virtual/PayPlugin 先例同款、vendor 实测），已按真实 FQCN use 导入。
2. **SignPluginTest 首轮失败一次**：`douyinAppPublicKey.pem` 相对路径少跳一级（`../../../Cert` → `../../../../Cert`），修正后全绿。属测试自身笔误，非实现问题。
3. **观察记录（未改动）**：手动对 tests/ 目录跑 php-cs-fixer 会要求测试类加 `@internal`/`@coversNothing` docblock；但 `.php-cs-fixer.php` 的 finder 仅 `in('src')`，tests/ 不在仓库 cs-fix 验收范围，且 master 老测试与 Task 2-4 测试均无该 docblock，故保持现状不加。

# 2026-09-05 13:00:00 (main agent 亲自验证)

- 文件级白名单：3 测试文件 → `OK (9 tests, 30 assertions)`。
- diff 内容级审查：SignPlugin `filter_params` 剔 `_` 键→json_encode(UNESCAPED_UNICODE)→getDouyinTradeSign('POST','/requestOrder',$data)；**setDirection(NoHttpRequestDirection) 在 $next 前、`$rocket=$next($rocket)` 后 setDestination({data, byteAuthorization})**——Wechat Virtual PayPlugin 模式精确复刻，9208 规避到位 ✅；空字段抛 PARAMS_NECESSARY_PARAMS_MISSING ✅；Query/QueryCps `_url` 精确（order_query//query_cps/）且零注入（无 app_id）✅；SignTest 从 byteAuthorization 正则反解 nonce_str/timestamp/signature 后用 fixture 公钥 `openssl_verify===1` 复算（plan 要求的反解验签路径）✅。
- Wave 3 收尾统一复验（Task 9 开工前）：cs-fix `Found 0 of 484` / analyse `[OK] No errors` / test `OK (1417 tests, 3472 assertions)`（1385+9+4+11+8=1417 自洽）。
- 结论：Task 5 通过，勾选 [x]。Wave 3 完成，准予进入 Wave 4。
