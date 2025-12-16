<?php

namespace App\Traits;

trait InteractsWithObjects
{
    /**
     * @param object|null $object
     * @param string $property
     * @return mixed
     */
    public static function getPropertyValueByObject(?object $object, string $property): mixed
    {
        if(is_null($object)) return null;
        return property_exists($object, $property) ? $object->{$property} : null;
    }
}
