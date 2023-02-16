<?php

namespace App\Enum;

use Nasyrov\Laravel\Enums\Enum;

class IdeapayStatusEnum extends Enum
{
    const PENDING = 1;
    const SUCCESS = 2;
    const FAILED = 3;
}