<?php

declare(strict_types=1);

namespace Arokettu\Bencode\Types;

use IteratorAggregate;

/**
 * @template-implements IteratorAggregate<int, mixed>
 */
final class ListType implements IteratorAggregate
{
    /** @use IterableTypeTrait<int> */
    use IterableTypeTrait;
}
