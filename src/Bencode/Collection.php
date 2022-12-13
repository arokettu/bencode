<?php

declare(strict_types=1);

namespace SandFox\Bencode\Bencode;

enum Collection
{
    case ARRAY;
    case ARRAY_OBJECT;
    case STDCLASS;
    public const OBJECT = self::STDCLASS;

    public function getHandler(): \Closure
    {
        return match ($this) {
            self::ARRAY         => fn (array $value) => $value,
            self::ARRAY_OBJECT  => fn (array $value) => new \ArrayObject($value, \ArrayObject::ARRAY_AS_PROPS),
            self::STDCLASS      => fn (array $value) => (object)$value,
        };
    }
}
