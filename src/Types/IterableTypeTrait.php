<?php

declare(strict_types=1);

namespace Arokettu\Bencode\Types;

use Traversable;

/**
 * @internal
 * @template TKey
 */
trait IterableTypeTrait
{
    private Traversable $traversable;

    /**
     * @psalm-api
     * @param iterable $iterable Iterable to be wrapped
     */
    public function __construct(iterable $iterable)
    {
        if (\is_array($iterable)) {
            $iterable = new \ArrayIterator($iterable);
        }

        $this->traversable = $iterable;
    }

    /**
     * @psalm-suppress ImplementedReturnTypeMismatch weird templated trait error
     * @return Traversable<TKey, mixed>
     */
    public function getIterator(): Traversable
    {
        return $this->traversable;
    }
}
