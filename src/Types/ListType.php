<?php

namespace SandFoxMe\Bencode\Types;

use SandFoxMe\Bencode\Exceptions\InvalidArgumentException;

class ListType implements \IteratorAggregate
{
    private $iterable;

    /**
     * ArrayType constructor.
     * @param $arrayOrTraversable
     */
    public function __construct($arrayOrTraversable)
    {
        if (is_array($arrayOrTraversable) === false && ($arrayOrTraversable instanceof \Traversable) === false) {
            throw new InvalidArgumentException('$arrayOrTraversable must be an array or implement \Traversable');
        }

        $this->iterable = $arrayOrTraversable;
    }

    public function getIterator()
    {
        return $this->iterable;
    }
}
