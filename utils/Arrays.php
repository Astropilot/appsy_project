<?php

class Arrays {

    public static function in_array_key($array, $key, $value) : bool {
        foreach ($array as $item) {
            if (property_exists($item, $key) && $item->{$key} == $value)
                return true;
        }
        return false;
    }
}
