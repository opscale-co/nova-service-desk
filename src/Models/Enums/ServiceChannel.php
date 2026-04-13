<?php

declare(strict_types=1);

namespace Opscale\NovaServiceDesk\Models\Enums;

enum ServiceChannel: string
{
    case Web = 'Web';
    case Chat = 'Chat';
    case Email = 'Email';
    case Phone = 'Phone';
}
