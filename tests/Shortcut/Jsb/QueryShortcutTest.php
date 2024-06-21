<?php

namespace Yansongda\Pay\Tests\Shortcut\Jsb;

use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Jsb\AddPayloadSignPlugin;
use Yansongda\Pay\Plugin\Jsb\AddRadarPlugin;
use Yansongda\Pay\Plugin\Jsb\Pay\Scan\QueryPlugin;
use Yansongda\Pay\Plugin\Jsb\ResponsePlugin;
use Yansongda\Pay\Plugin\Jsb\StartPlugin;
use Yansongda\Pay\Plugin\Jsb\VerifySignaturePlugin;
use Yansongda\Pay\Shortcut\Jsb\QueryShortcut;
use Yansongda\Pay\Tests\TestCase;

class QueryShortcutTest extends TestCase
{

	protected QueryShortcut $plugin;

	protected function setUp(): void
	{
		parent::setUp();

		$this->plugin = new QueryShortcut();
	}

	public function testDefault()
	{
		self::assertEquals([
			StartPlugin::class,
			QueryPlugin::class,
			AddPayloadSignPlugin::class,
			AddRadarPlugin::class,
			VerifySignaturePlugin::class,
			ResponsePlugin::class,
			ParserPlugin::class,
		], $this->plugin->getPlugins([]));
	}
}
