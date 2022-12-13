<?php

declare(strict_types=1);

namespace Arokettu\Bencode\Bencode;

enum Collection
{
    case ARRAY;
    case ARRAY_OBJECT;
    case STDCLASS;
    public const OBJECT = self::STDCLASS;

    public function getHandler(): \Closure
    {
        return match ($this) {
            self::ARRAY
                => fn (\Traversable $value) => iterator_to_array($value),
            self::ARRAY_OBJECT
                => fn (\Traversable $value) => new \ArrayObject(
                    iterator_to_array($value),
                    \ArrayObject::ARRAY_AS_PROPS
                ),
            self::STDCLASS
                => fn (\Traversable $value) => (object)iterator_to_array($value),
        };
    }
}
