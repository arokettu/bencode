<?php

declare(strict_types=1);

namespace SandFox\Bencode\Tests;

use Brick\Math\BigInteger;
use PHPUnit\Framework\TestCase;
use SandFox\Bencode\Exceptions\InvalidArgumentException;
use SandFox\Bencode\Types\BigIntType;

class BigIntTypeTest extends TestCase
{
    public function testPositiveNumericString(): void
    {
        $int = new BigIntType('123');
        self::assertEquals('123', $int->value);
    }

    public function testNegativeNumericString(): void
    {
        $int = new BigIntType('-123');
        self::assertEquals('-123', $int->getValue());
    }

    public function testZero(): void
    {
        $int = new BigIntType('0');
        self::assertEquals('0', (string)$int);
    }

    public function testNoPlus(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid integer string: '+123'");

        new BigIntType('+123');
    }

    public function testNoLeadingZero(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid integer string: '0123'");

        new BigIntType('0123');
    }

    public function testNoNegLeadingZero(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid integer string: '-0123'");

        new BigIntType('-0123');
    }

    public function testNoNegZero(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid integer string: '-0'");

        new BigIntType('-0');
    }

    public function testNoFloat(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid integer string: '12.34'");

        new BigIntType('12.34');
    }

    public function testNoHex(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid integer string: '0x1234'");

        new BigIntType('0x1234'); // @phpcs:ignore PHPCompatibility.Miscellaneous.ValidIntegers.HexNumericStringFound
    }

    public function testExport(): void
    {
        $int = new BigIntType('123');

        self::assertEquals(\gmp_init('123'), $int->toGMP());
        self::assertTrue((new \Math_BigInteger('123'))->equals($int->toPear()));
        self::assertTrue(BigInteger::of('123')->isEqualTo($int->toBrickMath()));
    }
}
