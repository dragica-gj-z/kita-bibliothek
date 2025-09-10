<?php

namespace App\Enums;

enum BookCondition: string
{
    case NEW = 'new';
    case USED = 'used';
    case DAMAGED = 'damaged';
    case MISSING_PAGES = 'missing_pages';
    case REPAIRED = 'repaired';
}
