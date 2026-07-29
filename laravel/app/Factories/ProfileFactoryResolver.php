<?php

namespace App\Factories;

use InvalidArgumentException;

class ProfileFactoryResolver
{
    public static function forRole(string $role): ProfileFactory
    {
        return match ($role) {
            'Student' => new StudentProfileFactory(),
            'Employer' => new EmployerProfileFactory(),
            'Admin' => new AdminProfileFactory(),
            default => throw new InvalidArgumentException("Invalid role: {$role}"),
        };
    }
}
