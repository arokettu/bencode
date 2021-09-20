<?php

declare(strict_types=1);

namespace SandFox\Bencode\Bencode;

enum Collection
{
    // todo: https://github.com/nikic/PHP-Parser/issues/807
    case ___I_AM_A_COVERAGE_BUG_HACK_IGNORE_ME___ARRAY;
    case OBJECT;
    public const ARRAY = self::___I_AM_A_COVERAGE_BUG_HACK_IGNORE_ME___ARRAY;
    public const STDCLASS = self::OBJECT;

    public function getHandler(): \Closure
    {
        return match ($this) {
            self::ARRAY  => fn ($value) => $value,
            self::OBJECT => fn ($value) => (object)$value,
        };
    }
}
