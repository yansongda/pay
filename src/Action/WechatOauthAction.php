<?php

declare(strict_types=1);

namespace Yansongda\Pay\Action;

enum WechatOauthAction: string
{
    case WebToken = 'web_token';
    case Refresh = 'refresh';
    case Userinfo = 'userinfo';
    case Session = 'session';
}
