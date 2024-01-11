<?php

namespace App\Enum;

enum NamedSetting: string
{
    case LatestRevision = 'latestRevision';
    case LatestRevisionDatetime = 'latestRevisionDatetime';
}
