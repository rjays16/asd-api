<?php

namespace App\Enum;

use Nasyrov\Laravel\Enums\Enum;

class ConventionMemberTypeEnum extends Enum
{
    const ASD_MEMBER = 1;
    const NON_ASD_MEMBER = 2;
    const RESIDENT_FELLOW = 3;
    const SPEAKER = 4;
}