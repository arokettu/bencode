<?php

declare(strict_types=1);

namespace Arokettu\Bencode\Types;

use IteratorAggregate;

/**
 * @template-implements IteratorAggregate<string, mixed>
 */
final class DictType implements IteratorAggregate
{
    /** @use IterableTypeTrait<string> */
    use IterableTypeTrait;
}
