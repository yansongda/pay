<?php

namespace Yansongda\Pay\Tests;

use PHPUnit\Framework\TestCase;

class PayTest extends TestCase
{
//    public function testGetContainerNullConfig()
//    {
//        $this->expectException(ContainerNotFoundException::class);
//        $this->expectExceptionCode(ContainerNotFoundException::CONTAINER_NOT_FOUND);
//        $this->expectExceptionMessage('You must init the container first with config');
//
//        Pay::getContainer();
//    }
//
//    public function testGetContainer()
//    {
//        self::assertInstanceOf(Container::class, Pay::getContainer([]));
//    }
//
//    public function testHasContainer()
//    {
//        self::assertFalse(Pay::hasContainer());
//
//        Pay::getContainer([]);
//
//        self::assertTrue(Pay::hasContainer());
//    }
//
//    public function testMagicCallNotFoundService()
//    {
//        $this->expectException(ServiceNotFoundException::class);
//
//        Pay::foo([]);
//    }
//
//    public function testMagicCallSetAndGet()
//    {
//        $data = [
//            'name' => 'yansongda',
//            'age' => 26
//        ];
//
//        Pay::getContainer([]);
//
//        Pay::set('profile', $data);
//
//        self::assertEquals($data, Pay::get('profile'));
//    }
//
//    public function testCoreServiceBase()
//    {
//        $container = Pay::getContainer([]);
//
//        self::assertInstanceOf(Container::class, $container);
//        self::assertInstanceOf(Pay::class, $container->get(ContainerInterface::class));
//        self::assertInstanceOf(Pay::class, Pay::get(Pay::class));
//    }
//
//    public function testCoreServiceConfig()
//    {
//        $config = [
//            'name' => 'yansongda',
//        ];
//
//        $container = Pay::getContainer($config);
//
//        self::assertInstanceOf(Config::class, $container->get(ConfigInterface::class));
//        self::assertEquals($config['name'], Pay::get(ConfigInterface::class)->get('name'));
//
//        // 修改 config 的情况
//        $config2 = [
//            'name' => 'yansongda2',
//        ];
//        Pay::set(ConfigInterface::class, new Config($config2));
//
//        self::assertEquals($config2['name'], Pay::get(ConfigInterface::class)->get('name'));
//    }
//
//    public function testCoreServiceLogger()
//    {
//        $container = Pay::getContainer([]);
//        $otherLogger = new \Monolog\Logger('test');
//
//        self::assertInstanceOf(Logger::class, $container->get(\Yansongda\Pay\Contract\LoggerInterface::class));
//        self::assertInstanceOf(\Monolog\Logger::class, $container->get(\Yansongda\Pay\Contract\LoggerInterface::class)->getLogger());
//        self::assertInstanceOf(LoggerInterface::class, $container->get(\Yansongda\Pay\Contract\LoggerInterface::class)->getLogger());
//        self::assertNotEquals($otherLogger, $container->get(\Yansongda\Pay\Contract\LoggerInterface::class));
//
//        $container->get(\Yansongda\Pay\Contract\LoggerInterface::class)->setLogger($otherLogger);
//        self::assertEquals($otherLogger, $container->get(\Yansongda\Pay\Contract\LoggerInterface::class)->getLogger());
//    }
//
//    public function testCoreServiceEvent()
//    {
//        $container = Pay::getContainer([]);
//
//        self::assertInstanceOf(EventDispatcher::class, $container->get(EventDispatcherInterface::class));
//    }
//
//    public function testCoreServiceHttpClient()
//    {
//        $container = Pay::getContainer([]);
//
//        self::assertInstanceOf(Client::class, $container->get(HttpClientInterface::class));
//    }
//
//    public function testCoreServiceExternalHttpClient()
//    {
//        Pay::getContainer([]);
//
//        $oldClient = Pay::get(HttpClientInterface::class);
//
//        $client = new Client(['timeout' => 3.0]);
//        Pay::set(HttpClientInterface::class, $client);
//
//        self::assertEquals($client, Pay::get(HttpClientInterface::class));
//        self::assertNotEquals($oldClient, Pay::get(HttpClientInterface::class));
//    }
//
//    public function testSingletonContainer()
//    {
//        $config1 = ['name' => 'yansongda'];
//        $config2 = ['age' => 26];
//
//        $container1 = Pay::getContainer($config1);
//        $container2 = Pay::getContainer($config2);
//
//        self::assertEquals($container1, $container2);
//    }
//
//    public function testGetNotFoundContainer()
//    {
//        $this->expectExceptionMessage('You must init the container first with config');
//        $this->expectException(ContainerNotFoundException::class);
//
//        Pay::getContainer();
//    }
}
