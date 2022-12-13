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
            self::ARRAY         => fn (iterable $value) => [...$value],
            self::ARRAY_OBJECT  => fn (iterable $value) => new \ArrayObject([...$value], \ArrayObject::ARRAY_AS_PROPS),
            self::STDCLASS      => fn (iterable $value) => (object)[...$value],
        };
    }
}
