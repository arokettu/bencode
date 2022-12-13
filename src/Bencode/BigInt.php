<?php

declare(strict_types=1);

namespace SandFox\Bencode\Bencode;

use Brick\Math\BigInteger;
use SandFox\Bencode\Exceptions\ParseErrorException;
use SandFox\Bencode\Types\BigIntType;

enum BigInt
{
    case NONE;
    case INTERNAL;
    case GMP;
    case BRICK_MATH;
    case PEAR;

    public function getHandler(): \Closure
    {
        return match ($this) {
            self::NONE       => fn (string $value) => throw new ParseErrorException(
                "Integer overflow: '{$value}'"
            ),
            self::INTERNAL   => fn (string $value) => new BigIntType($value),
            self::GMP        => fn (string $value) => \gmp_init($value),
            self::BRICK_MATH => fn (string $value) => BigInteger::of($value),
            self::PEAR       => fn (string $value) => new \Math_BigInteger($value),
        };
    }
}
