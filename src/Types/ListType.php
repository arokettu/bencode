<?php

declare(strict_types=1);

namespace Arokettu\Bencode\Types;

final class ListType implements \IteratorAggregate
{
    use IterableTypeTrait;
}
