<?php

/**
 * @copyright 2017 Anton Smirnov
 * @license MIT https://spdx.org/licenses/MIT.html
 */

declare(strict_types=1);

namespace Arokettu\Bencode\Bencode;

use Arokettu\Bencode\Exceptions\ParseErrorException;
use Arokettu\Bencode\Types\BigIntType;
use BcMath\Number;
use Brick\Math\BigInteger;
use Math_BigInteger;

enum BigInt
{
    case NONE;
    case INTERNAL;
    case GMP;
    case BRICK_MATH;
    case PEAR;
    case BCMATH;

    public function getHandler(): \Closure
    {
        /** @psalm-suppress InvalidArgument bad annotation in Math_BigInteger */
        return match ($this) {
            self::NONE
                => static fn (string $value) => throw new ParseErrorException(
                    "Integer overflow: '{$value}'",
                ),
            self::INTERNAL
                => static fn (string $value) => new BigIntType($value),
            self::GMP
                => static fn (string $value) => \gmp_init($value),
            self::BRICK_MATH
                => static fn (string $value) => BigInteger::of($value),
            self::PEAR
                => static fn (string $value) => new Math_BigInteger($value),
            self::BCMATH
                => static fn (string $value) => new Number($value),
        };
    }
}
