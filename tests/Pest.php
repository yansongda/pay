<?php

declare(strict_types=1);

use Yansongda\Pay\Tests\TestCase;

/**
 * Pest 与 PHPUnit 共存入口。
 *
 * 全目录绑定 TestCase：其 setUp() 注入全量多租户配置、tearDown() 执行 Pay::clear()，
 * 否则全局状态会在测试之间泄漏。类式 PHPUnit 测试仍使用自身的继承关系，不受影响。
 * 因此迁移过程中无需在此逐目录登记，各批次 PR 之间也不产生冲突。
 */
uses(TestCase::class)->in(__DIR__);
