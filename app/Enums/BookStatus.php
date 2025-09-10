<?php

namespace App\Enums;

enum BookStatus: string
{
    case AVAILABLE = 'available';
    case BORROWED  = 'borrowed';
    case RESERVED  = 'reserved';
    case LOST      = 'lost';
}
