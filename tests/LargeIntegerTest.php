<?php

declare(strict_types=1);

namespace SandFox\Bencode\Tests;

use Brick\Math\BigInteger;
use PHPUnit\Framework\TestCase;
use SandFox\Bencode\Bencode;
use SandFox\Bencode\Exceptions\ParseErrorException;
use SandFox\Bencode\Types\BigIntType;

class LargeIntegerTest extends TestCase
{
    private const POW_2_1024 =
        '1797693134862315907729305190789024733617976978942306572734300811577326758055009631' .
        '3270847732240753602112011387987139335765878976881441662249284743063947412437776789' .
        '3424865485276302219601246094119453082952085005768838150682342462881473913110540827' .
        '237163350510684586298239947245938479716304835356329624224137216';

    // common

    public function testDecodeLargeIntegerNoBigMath()
    {
        $encoded = 'i' . self::POW_2_1024 . 'e';

        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage("Invalid integer format or integer overflow: '" . self::POW_2_1024 . "'");
        Bencode::decode($encoded);
    }

    public function testDecodeLargeNegIntegerNoBigMath()
    {
        $encoded = 'i-' . self::POW_2_1024 . 'e';

        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage("Invalid integer format or integer overflow: '-" . self::POW_2_1024 . "'");
        Bencode::decode($encoded);
    }

    public function testDecodeLargeIntegerCallback()
    {
        $encoded = 'i' . self::POW_2_1024 . 'e';

        $decoded = Bencode::decode($encoded, ['bigInt' => function ($value) {
            return $value;
        }]);

        $this->assertEquals(self::POW_2_1024, $decoded);
    }

    public function testDecodeLargeIntegerClassName()
    {
        $encoded = 'i' . self::POW_2_1024 . 'e';

        $decoded = Bencode::decode($encoded, ['bigInt' => BigIntType::class]);

        $this->assertInstanceOf(BigIntType::class, $decoded);
        $this->assertEquals(self::POW_2_1024, $decoded->getValue());
    }

    // GMP

    public function testEncodeLargeIntegerGMP()
    {
        $largeInt = gmp_pow(2, 1024);
        $expected = 'i' . self::POW_2_1024 . 'e';

        $encoded = Bencode::encode($largeInt);

        $this->assertEquals($expected, $encoded);

        $largeNegInt = -$largeInt;
        $expected = 'i-' . self::POW_2_1024 . 'e';

        $encoded = Bencode::encode($largeNegInt);

        $this->assertEquals($expected, $encoded);
    }

    public function testDecodeLargeIntegerGMP()
    {
        $encoded = 'i' . self::POW_2_1024 . 'e';
        $expected = gmp_pow(2, 1024);

        $decoded = Bencode::decode($encoded, useGMP: true);
        $this->assertInstanceOf(GMP::class, $decoded);
        $this->assertEquals($expected, $decoded);

        $encodedNeg = 'i-' . self::POW_2_1024 . 'e';
        $expectedNeg = -gmp_pow(2, 1024);

        $decodedNeg = Bencode::decode($encodedNeg, ['useGMP' => true]);
        $this->assertInstanceOf(GMP::class, $decoded);
        $this->assertEquals($expectedNeg, $decodedNeg);
    }

    // Pear

    public function testEncodeLargeIntegerPear()
    {
        $largeInt = (new Math_BigInteger(1))->bitwise_leftShift(1024);
        $expected = 'i' . self::POW_2_1024 . 'e';

        $encoded = Bencode::encode($largeInt);

        $this->assertEquals($expected, $encoded);

        $largeNegInt = $largeInt->multiply(new Math_BigInteger(-1));
        $expected = 'i-' . self::POW_2_1024 . 'e';

        $encoded = Bencode::encode($largeNegInt);

        $this->assertEquals($expected, $encoded);
    }

    public function testDecodeLargeIntegerPear()
    {
        $encoded = 'i' . self::POW_2_1024 . 'e';
        $expected = (new Math_BigInteger(1))->bitwise_leftShift(1024);

        $decoded = Bencode::decode($encoded, ['bigInt' => Bencode\BigInt::PEAR]);
        $this->assertInstanceOf(Math_BigInteger::class, $decoded);
        $this->assertEquals($expected, $decoded);

        $encodedNeg = 'i-' . self::POW_2_1024 . 'e';
        $expectedNeg = $expected->multiply(new Math_BigInteger(-1));

        $decodedNeg = Bencode::decode($encodedNeg, ['bigInt' => Bencode\BigInt::PEAR]);
        $this->assertInstanceOf(Math_BigInteger::class, $decoded);
        $this->assertEquals($expectedNeg, $decodedNeg);
    }

    // brick/math

    public function testEncodeLargeIntegerBrick()
    {
        $largeInt = BigInteger::of(2)->power(1024);
        $expected = 'i' . self::POW_2_1024 . 'e';

        $encoded = Bencode::encode($largeInt);

        $this->assertEquals($expected, $encoded);

        $largeNegInt = $largeInt->multipliedBy(-1);
        $expected = 'i-' . self::POW_2_1024 . 'e';

        $encoded = Bencode::encode($largeNegInt);

        $this->assertEquals($expected, $encoded);
    }

    public function testDecodeLargeIntegerBrick()
    {
        $encoded = 'i' . self::POW_2_1024 . 'e';
        $expected = BigInteger::of(2)->power(1024);

        $decoded = Bencode::decode($encoded, ['bigInt' => Bencode\BigInt::BRICK_MATH]);
        $this->assertInstanceOf(BigInteger::class, $decoded);
        $this->assertEquals($expected, $decoded);

        $encodedNeg = 'i-' . self::POW_2_1024 . 'e';
        $expectedNeg = $expected->multipliedBy(-1);

        $decodedNeg = Bencode::decode($encodedNeg, ['bigInt' => Bencode\BigInt::BRICK_MATH]);
        $this->assertInstanceOf(BigInteger::class, $decoded);
        $this->assertEquals($expectedNeg, $decodedNeg);
    }

    // internal BigIntType

    public function testEncodeLargeIntegerInternal()
    {
        $largeInt = new BigIntType(self::POW_2_1024); // no math, just a string internally
        $expected = 'i' . self::POW_2_1024 . 'e';

        $encoded = Bencode::encode($largeInt);

        $this->assertEquals($expected, $encoded);

        $largeNegInt = new BigIntType('-' . self::POW_2_1024);
        $expected = 'i-' . self::POW_2_1024 . 'e';

        $encoded = Bencode::encode($largeNegInt);

        $this->assertEquals($expected, $encoded);
    }

    public function testDecodeLargeIntegerInternal()
    {
        $encoded = 'i' . self::POW_2_1024 . 'e';
        $expected = new BigIntType(self::POW_2_1024);

        $decoded = Bencode::decode($encoded, ['bigInt' => Bencode\BigInt::INTERNAL]);
        $this->assertInstanceOf(BigIntType::class, $decoded);
        $this->assertEquals($expected, $decoded);

        $encodedNeg = 'i-' . self::POW_2_1024 . 'e';
        $expectedNeg = new BigIntType('-' . self::POW_2_1024);

        $decodedNeg = Bencode::decode($encodedNeg, ['bigInt' => Bencode\BigInt::INTERNAL]);
        $this->assertInstanceOf(BigIntType::class, $decoded);
        $this->assertEquals($expectedNeg, $decodedNeg);
    }
}
