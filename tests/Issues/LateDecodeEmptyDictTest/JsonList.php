<?php

declare(strict_types=1);

namespace Arokettu\Bencode\Tests\Issues\LateDecodeEmptyDictTest;

use Generator;
use JsonSerializable as JsonSerializableAlias;

final class JsonList implements JsonSerializableAlias
{
    public function __construct(
        private readonly iterable $values,
    ) {
    }

    private function process(): Generator
    {
        foreach ($this->values as $value) {
            yield $value;
        }
    }

    public function jsonSerialize(): array
    {
        return iterator_to_array($this->process(), false);
    }
}
