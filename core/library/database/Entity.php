<?php

namespace core\library\database;

abstract class Entity
{
    public function getAttributes(){
        $reflectProperties = (new \ReflectionClass(static::class))->getProperties();

        $attributes = [];
        foreach ($reflectProperties as $property) {
            if ($property->isInitialized($this))
                $attributes[$property->getName()] = $property->getValue($this);
        }

        return $attributes;
    }
}
