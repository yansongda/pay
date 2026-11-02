<?php

declare(strict_types=1);

namespace Yansongda\Pay\Tests\Support\Traits;

/**
 * 微信虚拟支付回调 Helper。
 *
 * 按微信虚拟支付回调规则构造 AES-256-CBC 密文与 sha1(sort(token, timestamp, nonce, encrypt)) 签名。
 */
trait WechatVirtualCallbackHelpers
{
    /**
     * 43 字符的 EncodingAESKey，对应 32 字节 AES 密钥: base64_decode(key.'=') = '0123456789abcdef0123456789abcdef'.
     */
    protected string $encodingAesKey = 'MDEyMzQ1Njc4OWFiY2RlZjAxMjM0NTY3ODlhYmNkZWY';

    protected string $callbackToken = 'test_callback_token';

    private function encryptMessage(string $message, string $encodingAesKey, string $appId): string
    {
        $key = base64_decode($encodingAesKey.'=');
        $iv = substr($key, 0, 16);

        $random = str_repeat("\x00", 16);
        $content = $random.pack('N', strlen($message)).$message.$appId;

        // PKCS7 padding with 32-byte block size
        $blockSize = 32;
        $pad = $blockSize - (strlen($content) % $blockSize);
        $content .= str_repeat(chr($pad), $pad);

        $encrypted = openssl_encrypt($content, 'aes-256-cbc', $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);

        return base64_encode($encrypted);
    }

    private function createSignature(string $token, string $timestamp, string $nonce, string $encrypt): string
    {
        $arr = [$token, $timestamp, $nonce, $encrypt];
        sort($arr, SORT_STRING);

        return sha1(implode('', $arr));
    }
}
