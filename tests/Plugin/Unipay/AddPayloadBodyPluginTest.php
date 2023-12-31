<?php

namespace Plugin\Unipay;

use Yansongda\Pay\Plugin\Unipay\AddPayloadBodyPlugin;
use Yansongda\Pay\Rocket;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class AddPayloadBodyPluginTest extends TestCase
{
    protected AddPayloadBodyPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new AddPayloadBodyPlugin();
    }

    public function testNormal()
    {
        $payload = new Collection([
            "name" => "yansongda",
            'age' => 30,
        ]);

        $rocket = new Rocket();
        $rocket->setPayload($payload);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertSame($payload->query(), $result->getPayload()->get('_body'));
    }

    public function testUnderline()
    {
        $payload = new Collection([
            "name" => "yansongda",
            '_age' => 30,
            'aaa' => null,
        ]);

        $rocket = new Rocket();
        $rocket->setPayload($payload);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        unset($payload['_age'], $payload['aaa']);

        self::assertSame($payload->except('_age')->query(), $result->getPayload()->get('_body'));
    }

    public function testEmpty()
    {
        $payload = new Collection([
            '_age' => '30',
        ]);

        $rocket = new Rocket();
        $rocket->setPayload($payload);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertSame('', $result->getPayload()->get('_body'));
    }
}
