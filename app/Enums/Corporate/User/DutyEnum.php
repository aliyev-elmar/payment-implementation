<?php

namespace App\Enums\Corporate\User;

use ReflectionClass;

class DutyEnum
{
    const ADMIN = 1;
    const TEACHER = 2;
    const STUDENT = 3;
    const MENTOR = 4;

    /**
     * @return array
     */
    public static function getAllValues(): array
    {
        $reflectionClass = new ReflectionClass(__CLASS__);
        return $reflectionClass->getConstants();
    }
}
