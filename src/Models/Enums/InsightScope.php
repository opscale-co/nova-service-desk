<?php

declare(strict_types=1);

namespace Opscale\NovaServiceDesk\Models\Enums;

enum InsightScope: string
{
    case Business = 'Business';
    case Technical = 'Technical';
    case Legal = 'Legal';
    case Operational = 'Operational';
    case Other = 'Other';
}
