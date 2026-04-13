<?php

declare(strict_types=1);

namespace Opscale\NovaServiceDesk\Models\Enums;

enum SLAPolicyStatus: string
{
    case Active = 'Active';
    case Inactive = 'Inactive';
}
