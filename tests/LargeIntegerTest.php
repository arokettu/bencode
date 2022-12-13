<?php

declare(strict_types=1);

// phpcs:disable Generic.Files.LineLength.TooLong

namespace SandFox\Bencode\Tests;

use Brick\Math\BigInteger;
use PHPUnit\Framework\TestCase;
use SandFox\Bencode\Bencode;
use SandFox\Bencode\Exceptions\InvalidArgumentException;
use SandFox\Bencode\Exceptions\ParseErrorException;
use SandFox\Bencode\Types\BigIntType;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;

class LargeIntegerTest extends TestCase
{
    use ExpectDeprecationTrait;

    private const POW_2_1024 =
        '1797693134862315907729305190789024733617976978942306572734300811577326758055009631' .
        '3270847732240753602112011387987139335765878976881441662249284743063947412437776789' .
        '3424865485276302219601246094119453082952085005768838150682342462881473913110540827' .
        '237163350510684586298239947245938479716304835356329624224137216';

    // common

    public function testDecodeLargeIntegerNoBigMath(): void
    {
        $encoded = 'i' . self::POW_2_1024 . 'e';

        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage("Integer overflow: '" . self::POW_2_1024 . "'");
        Bencode::decode($encoded);
    }

    public function testDecodeLargeNegIntegerNoBigMath(): void
    {
        $encoded = 'i-' . self::POW_2_1024 . 'e';

        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage("Integer overflow: '-" . self::POW_2_1024 . "'");
        Bencode::decode($encoded);
    }

    public function testDecodeLargeIntegerCallback(): void
    {
        $encoded = 'i' . self::POW_2_1024 . 'e';

        $decoded = Bencode::decode($encoded, bigInt: function ($value) {
            return $value;
        });

        self::assertEquals(self::POW_2_1024, $decoded);
    }

    /**
     * @group legacy
     */
    public function testDecodeLargeIntegerClassName(): void
    {
        $this->expectDeprecation(
            'Since arokettu/bencode 3.1.0: Passing class names to listType, dictType, and bigInt is deprecated, use closures instead'
        );

        $encoded = 'i' . self::POW_2_1024 . 'e';

        $decoded = Bencode::decode($encoded, bigInt: BigIntType::class);

        self::assertInstanceOf(BigIntType::class, $decoded);
        self::assertEquals(self::POW_2_1024, $decoded->getValue());
    }

    public function testInvalidMode(): void
    {
        $encoded = 'i' . self::POW_2_1024 . 'e';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$bigInt must be Bencode\BigInt enum value, class name, or callback');

        Bencode::decode($encoded, bigInt: 'invalid');
    }

    // GMP

    public function testEncodeLargeIntegerGMP(): void
    {
        $largeInt = gmp_pow(2, 1024);
        $expected = 'i' . self::POW_2_1024 . 'e';

        $encoded = Bencode::encode($largeInt);

        self::assertEquals($expected, $encoded);

        $largeNegInt = -$largeInt;
        $expected = 'i-' . self::POW_2_1024 . 'e';

        $encoded = Bencode::encode($largeNegInt);

        self::assertEquals($expected, $encoded);
    }

    public function testDecodeLargeIntegerGMP(): void
    {
        $encoded = 'i' . self::POW_2_1024 . 'e';
        $expected = gmp_pow(2, 1024);

        $decoded = Bencode::decode($encoded, bigInt: Bencode\BigInt::GMP);
        self::assertInstanceOf(\GMP::class, $decoded);
        self::assertEquals($expected, $decoded);

        $encodedNeg = 'i-' . self::POW_2_1024 . 'e';
        $expectedNeg = -gmp_pow(2, 1024);

        $decodedNeg = Bencode::decode($encodedNeg, bigInt: Bencode\BigInt::GMP);
        self::assertInstanceOf(\GMP::class, $decoded);
        self::assertEquals($expectedNeg, $decodedNeg);
    }

    // Pear

    public function testEncodeLargeIntegerPear(): void
    {
        $largeInt = (new \Math_BigInteger(1))->bitwise_leftShift(1024);
        $expected = 'i' . self::POW_2_1024 . 'e';

        $encoded = Bencode::encode($largeInt);

        self::assertEquals($expected, $encoded);

        $largeNegInt = $largeInt->multiply(new \Math_BigInteger(-1));
        $expected = 'i-' . self::POW_2_1024 . 'e';

        $encoded = Bencode::encode($largeNegInt);

        self::assertEquals($expected, $encoded);
    }

    public function testDecodeLargeIntegerPear(): void
    {
        $encoded = 'i' . self::POW_2_1024 . 'e';
        $expected = (new \Math_BigInteger(1))->bitwise_leftShift(1024);

        $decoded = Bencode::decode($encoded, bigInt: Bencode\BigInt::PEAR);
        self::assertInstanceOf(\Math_BigInteger::class, $decoded);
        self::assertEquals($expected, $decoded);

        $encodedNeg = 'i-' . self::POW_2_1024 . 'e';
        $expectedNeg = $expected->multiply(new \Math_BigInteger(-1));

        $decodedNeg = Bencode::decode($encodedNeg, bigInt: Bencode\BigInt::PEAR);
        self::assertInstanceOf(\Math_BigInteger::class, $decoded);
        self::assertEquals($expectedNeg, $decodedNeg);
    }

    // brick/math

    public function testEncodeLargeIntegerBrick(): void
    {
        $largeInt = BigInteger::of(2)->power(1024);
        $expected = 'i' . self::POW_2_1024 . 'e';

        $encoded = Bencode::encode($largeInt);

        self::assertEquals($expected, $encoded);

        $largeNegInt = $largeInt->multipliedBy(-1);
        $expected = 'i-' . self::POW_2_1024 . 'e';

        $encoded = Bencode::encode($largeNegInt);

        self::assertEquals($expected, $encoded);
    }

    public function testDecodeLargeIntegerBrick(): void
    {
        $encoded = 'i' . self::POW_2_1024 . 'e';
        $expected = BigInteger::of(2)->power(1024);

        $decoded = Bencode::decode($encoded, bigInt: Bencode\BigInt::BRICK_MATH);
        self::assertInstanceOf(BigInteger::class, $decoded);
        self::assertEquals($expected, $decoded);

        $encodedNeg = 'i-' . self::POW_2_1024 . 'e';
        $expectedNeg = $expected->multipliedBy(-1);

        $decodedNeg = Bencode::decode($encodedNeg, bigInt: Bencode\BigInt::BRICK_MATH);
        self::assertInstanceOf(BigInteger::class, $decoded);
        self::assertEquals($expectedNeg, $decodedNeg);
    }

    // internal BigIntType

    public function testEncodeLargeIntegerInternal(): void
    {
        $largeInt = new BigIntType(self::POW_2_1024); // no math, just a string internally
        $expected = 'i' . self::POW_2_1024 . 'e';

        $encoded = Bencode::encode($largeInt);

        self::assertEquals($expected, $encoded);

        $largeNegInt = new BigIntType('-' . self::POW_2_1024);
        $expected = 'i-' . self::POW_2_1024 . 'e';

        $encoded = Bencode::encode($largeNegInt);

        self::assertEquals($expected, $encoded);
    }

    public function testDecodeLargeIntegerInternal(): void
    {
        $encoded = 'i' . self::POW_2_1024 . 'e';
        $expected = new BigIntType(self::POW_2_1024);

        $decoded = Bencode::decode($encoded, bigInt: Bencode\BigInt::INTERNAL);
        self::assertInstanceOf(BigIntType::class, $decoded);
        self::assertEquals($expected, $decoded);

        $encodedNeg = 'i-' . self::POW_2_1024 . 'e';
        $expectedNeg = new BigIntType('-' . self::POW_2_1024);

        $decodedNeg = Bencode::decode($encodedNeg, bigInt: Bencode\BigInt::INTERNAL);
        self::assertInstanceOf(BigIntType::class, $decoded);
        self::assertEquals($expectedNeg, $decodedNeg);
    }
}
