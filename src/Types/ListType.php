<?php

declare(strict_types=1);

namespace SandFox\Bencode\Types;

class ListType implements \IteratorAggregate
{
    private \Traversable $traversable;

    /**
     * @param iterable|\stdClass $iterable Iterable to be wrapped
     */
    public function __construct(iterable|\stdClass $iterable)
    {
        if (is_array($iterable)) {
            $iterable = new \ArrayIterator($iterable);
        }

        if ($iterable instanceof \stdClass) {
            $iterable = (static function (\stdClass $iterable) {
                foreach ($iterable as $value) {
                    yield $value;
                }
            })($iterable);
        }

        $this->setIterator($iterable);
    }

    private function setIterator(\Traversable $traversable): void
    {
        $this->traversable = $traversable;
    }

    public function getIterator(): \Traversable
    {
        return $this->traversable;
    }
}
