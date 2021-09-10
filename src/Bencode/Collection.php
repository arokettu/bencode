<?php

declare(strict_types=1);

namespace SandFox\Bencode\Bencode;

enum Collection
{
    case ARRAY;
    case OBJECT;
    public const STDCLASS = self::OBJECT;

    public function getHandler(): \Closure
    {
        return match ($this) {
            self::ARRAY  => fn ($value) => $value,
            self::OBJECT => fn ($value) => (object)$value,
        };
    }
}
