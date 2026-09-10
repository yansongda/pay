<?php

declare(strict_types=1);

namespace Yansongda\Pay\Action;

enum WechatPayScoreAction: string
{
    case Default = 'default';
    case Create = 'create';
    case Query = 'query';
    case Cancel = 'cancel';
    case Complete = 'complete';
    case Modify = 'modify';
    case Sync = 'sync';
    case Pay = 'pay';
    case Permissions = 'permissions';
    case PermissionsQuery = 'permissions_query';
    case PermissionsTerminate = 'permissions_terminate';
}
