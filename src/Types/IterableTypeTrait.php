<?php

declare(strict_types=1);

namespace SandFox\Bencode\Types;

/**
 * @internal
 */
trait IterableTypeTrait
{
    private \Traversable $traversable;

    /**
     * @param iterable $iterable Iterable to be wrapped
     */
    public function __construct(iterable $iterable)
    {
        if (\is_array($iterable)) {
            $iterable = new \ArrayIterator($iterable);
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
