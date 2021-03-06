<?php

declare(strict_types=1);

namespace SandFox\Bencode\Tests;

use Brick\Math\BigInteger;
use PHPUnit\Framework\TestCase;
use SandFox\Bencode\Exceptions\InvalidArgumentException;
use SandFox\Bencode\Types\BigIntType;

class BigIntTypeTest extends TestCase
{
    public function testPositiveNumericString()
    {
        $int = new BigIntType('123');
        self::assertEquals('123', $int->getValue());
    }

    public function testNegativeNumericString()
    {
        $int = new BigIntType('-123');
        self::assertEquals('-123', $int->getValue());
    }

    public function testZero()
    {
        $int = new BigIntType('0');
        self::assertEquals('0', $int->getValue());
    }

    public function testNoPlus()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid integer string: '+123'");

        new BigIntType('+123');
    }

    public function testNoLeadingZero()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid integer string: '0123'");

        new BigIntType('0123');
    }

    public function testNoNegLeadingZero()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid integer string: '-0123'");

        new BigIntType('-0123');
    }

    public function testNoNegZero()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid integer string: '-0'");

        new BigIntType('-0');
    }

    public function testNoFloat()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid integer string: '12.34'");

        new BigIntType('12.34');
    }

    public function testNoHex()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid integer string: '0x1234'");

        new BigIntType('0x1234'); // @phpcs:ignore PHPCompatibility.Miscellaneous.ValidIntegers.HexNumericStringFound
    }

    public function testExport()
    {
        $int = new BigIntType('123');

        self::assertEquals(gmp_init('123'), $int->toGMP());
        self::assertEquals(true, (new \Math_BigInteger('123'))->equals($int->toPear()));
        self::assertEquals(true, BigInteger::of('123')->isEqualTo($int->toBrickMath()));
    }
}
