<?php

/**
 * @copyright 2017 Anton Smirnov
 * @license MIT https://spdx.org/licenses/MIT.html
 */

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
                => static fn (\Traversable $value) => iterator_to_array($value),
            self::ARRAY_OBJECT
                => static fn (\Traversable $value) => new \ArrayObject(
                    iterator_to_array($value),
                    \ArrayObject::ARRAY_AS_PROPS,
                ),
            self::STDCLASS
                => static fn (\Traversable $value) => (object)iterator_to_array($value),
        };
    }
}
