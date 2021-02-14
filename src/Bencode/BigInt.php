<?php

declare(strict_types=1);

namespace SandFox\Bencode\Bencode;

final class BigInt
{
    public const NONE       = 'none';
    public const INTERNAL   = 'internal';
    public const GMP        = 'ext-gmp';
    public const BRICK_MATH = 'brick/math';
    public const PEAR       = 'pear/math_biginteger';
}
