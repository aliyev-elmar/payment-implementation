<?php

if (!function_exists('object_get')) {
    function object_get(?object $object, string $key, mixed $default = null): mixed
    {
        if (is_null($object)) {
            return $default;
        }

        return property_exists($object, $key) ? $object->{$key} : $default;
    }
}
