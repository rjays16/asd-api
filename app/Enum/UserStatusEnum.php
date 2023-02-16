<?php

namespace App\Enum;

use Nasyrov\Laravel\Enums\Enum;

class UserStatusEnum extends Enum
{
    const IMPORTED_PENDING = 1;
    const ORDERED = 2;
    const REGISTERED = 3;
    const DECLINED = 4;
}