<?php

namespace Yansongda\Pay\Tests\Shortcut\Epay;

use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Epay\AddPayloadSignPlugin;
use Yansongda\Pay\Plugin\Epay\AddRadarPlugin;
use Yansongda\Pay\Plugin\Epay\Pay\Scan\PrepayPlugin;
use Yansongda\Pay\Plugin\Epay\ResponsePlugin;
use Yansongda\Pay\Plugin\Epay\StartPlugin;
use Yansongda\Pay\Plugin\Epay\VerifySignaturePlugin;
use Yansongda\Pay\Shortcut\Epay\ScanShortcut;
use Yansongda\Pay\Tests\TestCase;

class ScanShortcutTest extends TestCase
{
	protected ScanShortcut $plugin;

	protected function setUp(): void
	{
		parent::setUp();

		$this->plugin = new ScanShortcut();
	}

	public function testDefault()
	{
		self::assertEquals([
			StartPlugin::class,
			PrepayPlugin::class,
			AddPayloadSignPlugin::class,
			AddRadarPlugin::class,
			VerifySignaturePlugin::class,
			ResponsePlugin::class,
			ParserPlugin::class,
		], $this->plugin->getPlugins([]));
	}
}
