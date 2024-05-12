<?php

namespace core\library\database;

abstract class Entity
{
    public function getProperties(): array
    {
        $reflectProperties = (new \ReflectionClass(static::class))->getProperties();

        $properties = [];
        foreach ($reflectProperties as $property) {
            if ($property->isInitialized($this))
                $properties[$property->getName()] = $property->getValue($this);
        }

        return $properties;
    }

    public function set(array $properties): void
    {
        foreach ($properties as $name => $value) {
            if (property_exists($this, $name))
                $this->$name = $value;
            else
                throw new \Exception("This property \"{$name}\" not exist in " . static::class . "!");
        }
    }
}
