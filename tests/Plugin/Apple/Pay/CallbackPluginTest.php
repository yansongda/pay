<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Plugin\Apple\Pay;

use GuzzleHttp\Psr7\ServerRequest;
use RuntimeException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Rocket;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidSignException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Plugin\Apple\Pay\CallbackPlugin;
use Yansongda\Pay\Tests\Support\Apple\AppleJwsFactory;
use Yansongda\Pay\Tests\TestCase;
use Yansongda\Supports\Collection;

class CallbackPluginTest extends TestCase
{
    protected CallbackPlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = new CallbackPlugin();
    }

    public function testCallbackSuccess()
    {
        $signedTransactionInfo = AppleJwsFactory::makeJws([
            'transactionId' => '1000001234567890',
            'originalTransactionId' => '1000001234567890',
            'bundleId' => 'com.yansongda.pay.test',
            'productId' => 'com.yansongda.pay.vip',
            'purchaseDate' => 1691234567890,
            'originalPurchaseDate' => 1691234567890,
            'type' => 'Auto-Renewable Subscription',
            'inAppOwnershipType' => 'PURCHASED',
            'signedDate' => 1691234567890,
        ]);
        $payload = [
            'notificationType' => 'SUBSCRIBED',
            'subtype' => 'INITIAL_BUY',
            'notificationUUID' => '85b2f1e6-5c9a-4c3b-8f2e-9d1a4b6c7d8e',
            'signedDate' => 1691234567890,
            'version' => '2.0',
            'data' => [
                'appAppleId' => 123456789,
                'bundleId' => 'com.yansongda.pay.test',
                'environment' => 'Sandbox',
                'signedTransactionInfo' => $signedTransactionInfo,
            ],
        ];
        $jws = AppleJwsFactory::makeJws($payload);

        $request = new ServerRequest('POST', 'https://pay.yansongda.cn/apple/notify', [
            'Content-Type' => 'application/json',
        ], json_encode(['signedPayload' => $jws]));

        $rocket = new Rocket();
        $rocket->setParams(['_request' => $request, '_params' => []]);

        $result = $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });

        self::assertInstanceOf(Collection::class, $result->getPayload());
        self::assertNotEmpty($result->getPayload()->all());
        self::assertEquals('SUBSCRIBED', $result->getPayload()->get('notificationType'));
        self::assertEquals('INITIAL_BUY', $result->getPayload()->get('subtype'));
        self::assertEquals('85b2f1e6-5c9a-4c3b-8f2e-9d1a4b6c7d8e', $result->getPayload()->get('notificationUUID'));
        self::assertIsArray($result->getPayload()->get('data.signedTransactionInfo'));
        self::assertEquals('1000001234567890', $result->getPayload()->get('data.signedTransactionInfo.transactionId'));
    }

    public function testInvalidBodyThrowsException()
    {
        $request = new ServerRequest('POST', 'https://pay.yansongda.cn/apple/notify', [], 'not-valid-json');

        $rocket = new Rocket();
        $rocket->setParams(['_request' => $request, '_params' => []]);

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_APPLE_TOKEN_INVALID);

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testMissingSignedPayloadThrowsException()
    {
        $request = new ServerRequest('POST', 'https://pay.yansongda.cn/apple/notify', [], json_encode(['foo' => 'bar']));

        $rocket = new Rocket();
        $rocket->setParams(['_request' => $request, '_params' => []]);

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_EMPTY);

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testTamperedPayloadThrowsException()
    {
        $jws = AppleJwsFactory::makeJws(['notificationType' => 'SUBSCRIBED']);
        $tampered = self::tamperJwsPayload($jws, ['notificationType' => 'EVIL']);

        $request = new ServerRequest('POST', 'https://pay.yansongda.cn/apple/notify', [], json_encode(['signedPayload' => $tampered]));

        $rocket = new Rocket();
        $rocket->setParams(['_request' => $request, '_params' => []]);

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_ERROR);

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testTamperedNestedJwsThrowsException()
    {
        $inner = AppleJwsFactory::makeJws(['transactionId' => '1000001234567890']);
        $payload = [
            'notificationType' => 'SUBSCRIBED',
            'data' => [
                'bundleId' => 'com.yansongda.pay.test',
                'environment' => 'Sandbox',
                'signedTransactionInfo' => $inner,
            ],
        ];
        $payload['data']['signedTransactionInfo'] = self::tamperJwsPayload($inner, ['transactionId' => 'EVIL']);
        $jws = AppleJwsFactory::makeJws($payload);

        $request = new ServerRequest('POST', 'https://pay.yansongda.cn/apple/notify', [], json_encode(['signedPayload' => $jws]));

        $rocket = new Rocket();
        $rocket->setParams(['_request' => $request, '_params' => []]);

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_ERROR);

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testInnerJwsWithoutX5cRejected()
    {
        // 对齐官方库：二级 JWS 须自带 x5c，无 x5c 一律拒绝
        $inner = self::makeInnerJws(['transactionId' => '1000001234567890']);
        $jws = AppleJwsFactory::makeJws([
            'notificationType' => 'SUBSCRIBED',
            'data' => [
                'bundleId' => 'com.yansongda.pay.test',
                'environment' => 'Sandbox',
                'signedTransactionInfo' => $inner,
            ],
        ]);

        $request = new ServerRequest('POST', 'https://pay.yansongda.cn/apple/notify', [], json_encode(['signedPayload' => $jws]));

        $rocket = new Rocket();
        $rocket->setParams(['_request' => $request, '_params' => []]);

        self::expectException(InvalidSignException::class);
        self::expectExceptionCode(Exception::SIGN_ERROR);

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testInvalidRequestThrowsException()
    {
        $rocket = new Rocket();
        $rocket->setParams(['_request' => ['foo' => 'bar'], '_params' => []]);

        self::expectException(InvalidParamsException::class);
        self::expectExceptionCode(Exception::PARAMS_CALLBACK_REQUEST_INVALID);

        $this->plugin->assembly($rocket, function ($rocket) { return $rocket; });
    }

    public function testProviderCallbackLinks()
    {
        $jws = AppleJwsFactory::makeJws([
            'notificationType' => 'DID_RENEW',
            'subtype' => 'RESUBSCRIBE',
            'notificationUUID' => '85b2f1e6-5c9a-4c3b-8f2e-9d1a4b6c7d8e',
            'signedDate' => 1691234567890,
            'version' => '2.0',
            'data' => [
                'bundleId' => 'com.yansongda.pay.test',
                'environment' => 'Sandbox',
                'signedTransactionInfo' => AppleJwsFactory::makeJws([
                    'transactionId' => '1000001234567890',
                    'originalTransactionId' => '1000001234567890',
                    'bundleId' => 'com.yansongda.pay.test',
                    'productId' => 'com.yansongda.pay.vip',
                    'purchaseDate' => 1691234567890,
                    'originalPurchaseDate' => 1691234567890,
                    'type' => 'Auto-Renewable Subscription',
                    'inAppOwnershipType' => 'PURCHASED',
                    'signedDate' => 1691234567890,
                ]),
            ],
        ]);

        $request = new ServerRequest('POST', 'https://pay.yansongda.cn/apple/notify', [
            'Content-Type' => 'application/json',
        ], json_encode(['signedPayload' => $jws]));

        $result = Pay::apple()->callback($request);

        self::assertInstanceOf(Collection::class, $result);
        self::assertEquals('DID_RENEW', $result->get('notificationType'));
        self::assertEquals('RESUBSCRIBE', $result->get('subtype'));
        self::assertEquals('85b2f1e6-5c9a-4c3b-8f2e-9d1a4b6c7d8e', $result->get('notificationUUID'));
        self::assertIsArray($result->get('data.signedTransactionInfo'));
        self::assertEquals('1000001234567890', $result->get('data.signedTransactionInfo.transactionId'));
    }

    /**
     * 构造无 x5c 的二级 JWS（signedTransactionInfo/signedRenewalInfo）——反路径专用：
     * 对齐官方库后，二级 JWS 须自带 x5c，此形态应被拒绝。
     *
     * @param array<string, mixed> $payload
     */
    private static function makeInnerJws(array $payload): string
    {
        $headerB64 = self::base64UrlEncode((string) json_encode(['alg' => 'ES256']));
        $payloadB64 = self::base64UrlEncode((string) json_encode($payload));

        $privateKey = openssl_pkey_get_private((string) file_get_contents(dirname(__DIR__, 3).'/Cert/apple/jws-leaf.key'));

        if (false === $privateKey) {
            throw new RuntimeException('读取 jws-leaf 私钥失败');
        }

        $derSignature = '';

        if (!openssl_sign($headerB64.'.'.$payloadB64, $derSignature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('ES256 签名失败');
        }

        return $headerB64.'.'.$payloadB64.'.'.self::base64UrlEncode(self::derToRaw($derSignature));
    }

    /**
     * 篡改 JWS 的 payload 段（保持 header/签名不变，用于反路径用例）。
     *
     * @param array<string, mixed> $payload
     */
    private static function tamperJwsPayload(string $jws, array $payload): string
    {
        $parts = explode('.', $jws);
        $parts[1] = self::base64UrlEncode((string) json_encode($payload));

        return implode('.', $parts);
    }

    /**
     * ECDSA DER 签名 → raw（r || s，各 32 字节），测试侧独立实现。
     */
    private static function derToRaw(string $der): string
    {
        $offset = 2;
        $parts = [];

        for ($i = 0; $i < 2; ++$i) {
            if ("\x02" !== ($der[$offset] ?? '')) {
                throw new RuntimeException('ECDSA DER 签名结构非法');
            }

            $length = ord($der[++$offset]);
            ++$offset;
            $value = substr($der, $offset, $length);
            $offset += $length;

            // 剥前导 0x00（符号位填充，仅当下一字节高位为 1，DER 最小编码保证可安全剥除）
            if (1 < strlen($value) && "\x00" === $value[0]) {
                $value = substr($value, 1);
            }

            $parts[] = $value;
        }

        return str_pad((string) $parts[0], 32, "\x00", STR_PAD_LEFT).str_pad((string) $parts[1], 32, "\x00", STR_PAD_LEFT);
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
