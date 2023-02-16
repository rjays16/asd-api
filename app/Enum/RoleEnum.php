<?php

namespace App\Enum;

use Nasyrov\Laravel\Enums\Enum;

class RoleEnum extends Enum
{
    const SUPER_ADMIN = 1;
    const ADMIN = 2;
    const CONVENTION_MEMBER = 3;
}