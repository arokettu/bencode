<?php

namespace SandFoxMe\Bencode\Types;

class ListType implements \IteratorAggregate
{
    private $traversable;

    /**
     * ArrayType constructor.
     * @param iterable|array|\Traversable $iterable
     */
    public function __construct($iterable)
    {
        if (is_array($iterable)) {
            $iterable = new \ArrayIterator($iterable);
        }

        $this->setIterator($iterable);
    }

    private function setIterator(\Traversable $traversable)
    {
        $this->traversable = $traversable;
    }

    public function getIterator()
    {
        return $this->traversable;
    }
}
