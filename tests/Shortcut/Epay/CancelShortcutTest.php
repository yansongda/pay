<?php

namespace Yansongda\Pay\Tests\Shortcut\Epay;

use Yansongda\Artful\Plugin\ParserPlugin;
use Yansongda\Pay\Plugin\Epay\AddPayloadSignPlugin;
use Yansongda\Pay\Plugin\Epay\AddRadarPlugin;
use Yansongda\Pay\Plugin\Epay\Pay\Scan\CancelPlugin;
use Yansongda\Pay\Plugin\Epay\ResponsePlugin;
use Yansongda\Pay\Plugin\Epay\StartPlugin;
use Yansongda\Pay\Plugin\Epay\VerifySignaturePlugin;
use Yansongda\Pay\Shortcut\Epay\CancelShortcut;
use Yansongda\Pay\Tests\TestCase;

class CancelShortcutTest extends TestCase
{

	protected CancelShortcut $plugin;

	protected function setUp(): void
	{
		parent::setUp();

		$this->plugin = new CancelShortcut();
	}


	public function testDefault()
	{
		self::assertEquals([
			StartPlugin::class,
			CancelPlugin::class,
			AddPayloadSignPlugin::class,
			AddRadarPlugin::class,
			VerifySignaturePlugin::class,
			ResponsePlugin::class,
			ParserPlugin::class,
		], $this->plugin->getPlugins([]));
	}
}
