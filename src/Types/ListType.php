<?php

namespace SandFoxMe\Bencode\Types;

class ListType implements \IteratorAggregate
{
    private $traversable;

    /**
     * ArrayType constructor.
     * @param \Traversable $traversable
     */
    public function __construct(\Traversable $traversable)
    {
        $this->traversable = $traversable;
    }

    public function getIterator()
    {
        return $this->traversable;
    }
}
