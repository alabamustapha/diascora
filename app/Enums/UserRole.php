<?php

namespace App\Enums;

enum UserRole: string
{
    case Sysadmin = 'sysadmin';
    case Admin = 'admin';
    case Manager = 'manager';
}
