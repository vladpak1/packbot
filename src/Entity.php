<?php

namespace PackBot;

/**
 * Entity is a class that represents some data entity that can be converted to json.
 */
abstract class Entity implements \JsonSerializable
{
    /**
     * Serialize to json all properties of the class.
     * @return string json
     */
    abstract public function jsonSerialize(): string;

    /**
     * Converting to string.
     */
    abstract public function __toString(): string;

    /**
     * Get the data as an object.
     */
    abstract public function getRawData(): object;
}
