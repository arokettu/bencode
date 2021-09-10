<?php

declare(strict_types=1);

namespace SandFox\Bencode\Bencode;

use Brick\Math\BigInteger;
use SandFox\Bencode\Exceptions\ParseErrorException;
use SandFox\Bencode\Types\BigIntType;

enum BigInt
{
    case None;
    case Internal;
    case GMP;
    case PEAR;
    case BrickMath;

    // aliases
    public const NONE       = self::None;
    public const INTERNAL   = self::Internal;
    public const BRICK_MATH = self::BrickMath;

    public function getHandler(): \Closure
    {
        return match ($this) {
            self::None       => fn ($value) => throw new ParseErrorException(
                "Integer overflow: '{$value}'"
            ),
            self::Internal  => fn ($value) => new BigIntType($value),
            self::GMP       => fn ($value) => \gmp_init($value),
            self::PEAR      => fn ($value) => new \Math_BigInteger($value),
            self::BrickMath => fn ($value) => BigInteger::of($value),
        };
    }
}
