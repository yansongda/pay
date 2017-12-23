<?php

namespace Yansongda\Pay\Tests\Support;

use Yansongda\Pay\Exceptions\InvalidArgumentException;
use Yansongda\Pay\Support\Config;
use Yansongda\Pay\Tests\TestCase;

class ConfigTest extends TestCase
{
    public function testGetConfig()
    {
        $array = [
            'foo' => 'bar',
            'bar' => [
                'id'  => '18',
                'key' => [
                    'public'  => 'qwer',
                    'private' => 'asdf',
                ],
            ],
        ];

        $config = new Config($array);

        $this->assertTrue(isset($config['foo']));

        $this->assertSame('bar', $config['foo']);
        $this->assertSame('bar', $config->get('foo'));
        $this->assertSame($array, $config->get());

        $this->assertSame('qwer', $config->get('bar.key.public'));
        $this->assertNull($config->get('bar.key.public.foo'));
        $this->assertNull($config->get('bar.foo.foo.foo'));
    }

    public function testSetConfig()
    {
        $config = new Config([]);

        $this->assertArrayHasKey('foo', $config->set('foo', 'bar'));
        $this->assertSame('bar', $config->get('foo'));

        $this->assertArrayHasKey('bar', $config->set('bar.key.public', 'qwer'));
        $this->assertSame('qwer', $config->get('bar.key.public'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid config key.');
        $config->set('', '');
        $this->assertArrayHasKey('error', $config->set('error.foo.foo.foo.foo', 'foo'));
    }
}
