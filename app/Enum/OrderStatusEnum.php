<?php

namespace App\Enum;

use Nasyrov\Laravel\Enums\Enum;

class OrderStatusEnum extends Enum
{
    const PENDING = 1;
    const COMPLETED = 2;
    const FAILED = 3;
}