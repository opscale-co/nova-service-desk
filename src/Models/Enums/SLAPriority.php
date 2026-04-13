<?php

declare(strict_types=1);

namespace Opscale\NovaServiceDesk\Models\Enums;

enum SLAPriority: string
{
    case Critical = 'Critical';
    case High = 'High';
    case Medium = 'Medium';
    case Low = 'Low';
    case Planning = 'Planning';
}
