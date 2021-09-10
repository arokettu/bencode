<?php

declare(strict_types=1);

namespace SandFox\Bencode\Bencode;

enum Collection
{
    case Array;
    case Object;
    public const stdClass   = self::Object;

    // aliases
    public const ARRAY      = self::Array;
    public const OBJECT     = self::Object;
    public const STDCLASS   = self::Object;

    public function getHandler(): \Closure
    {
        return match ($this) {
            self::Array  => fn ($value) => $value,
            self::Object => fn ($value) => (object)$value,
        };
    }
}
