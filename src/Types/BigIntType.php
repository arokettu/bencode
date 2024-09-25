<?php

declare(strict_types=1);

namespace Arokettu\Bencode\Types;

use Arokettu\Bencode\Exceptions\InvalidArgumentException;
use Arokettu\Bencode\Util\IntUtil;
use BcMath\Number;
use Brick\Math\BigInteger;
use Math_BigInteger;

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

    /**
     * @psalm-api
     */
    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * @psalm-api
     */
    public function toGMP(): \GMP
    {
        return \gmp_init($this->value);
    }

    /**
     * @psalm-api
     */
    public function toPear(): Math_BigInteger
    {
        /** @psalm-suppress InvalidArgument bad annotation in Math_BigInteger */
        return new Math_BigInteger($this->value);
    }

    /**
     * @psalm-api
     */
    public function toBrickMath(): BigInteger
    {
        return BigInteger::of($this->value);
    }

    /**
     * @psalm-api
     */
    public function toBcMath(): Number
    {
        return new Number($this->value);
    }
}
