<?php

namespace App\Enums;

use phpDocumentor\Reflection\Types\Boolean;

enum PageType: string
{
    case Public     = 'public';
    case Profile    = 'profile';
    case Admin      = 'admin';
}

