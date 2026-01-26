<?php

namespace Opscale\NovaServiceDesk\Models\Enums;

enum ServiceChannel: string
{
    case Web = 'Web';
    case Chat = 'Chat';
    case Email = 'Email';
    case Phone = 'Phone';
}
