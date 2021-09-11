<?php

declare(strict_types=1);

namespace SandFox\Bencode\Types;

use Brick\Math\BigInteger;
use SandFox\Bencode\Exceptions\InvalidArgumentException;
use SandFox\Bencode\Util\IntUtil;

final class BigIntType implements \Stringable
{
    public function __construct(public readonly string $value)
    {
        $this->assertValidInteger($value);
    }

    private function assertValidInteger(string $value): void
    {
        if (!IntUtil::isValid($value)) {
            throw new InvalidArgumentException("Invalid integer string: '{$value}'");
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function toGMP(): \GMP
    {
        return \gmp_init($this->value);
    }

    public function toPear(): \Math_BigInteger
    {
        return new \Math_BigInteger($this->value);
    }

    public function toBrickMath(): BigInteger
    {
        return BigInteger::of($this->value);
    }
}
