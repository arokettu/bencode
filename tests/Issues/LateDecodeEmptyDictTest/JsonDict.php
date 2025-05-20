<?php

declare(strict_types=1);

namespace Arokettu\Bencode\Tests\Issues\LateDecodeEmptyDictTest;

use ArrayObject;
use Generator;
use JsonSerializable;

final class JsonDict implements JsonSerializable
{
    public function __construct(
        private readonly iterable $values,
    ) {
    }

    private function process(): Generator
    {
        foreach ($this->values as $key => $value) {
            yield $key => $value;
        }
    }

    public function jsonSerialize(): ArrayObject
    {
        return new ArrayObject(iterator_to_array($this->process()));
    }
}
