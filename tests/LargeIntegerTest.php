<?php

declare(strict_types=1);

namespace SandFox\Bencode\Tests;

use GMP;
use PHPUnit\Framework\TestCase;
use SandFox\Bencode\Bencode;
use SandFox\Bencode\Exceptions\ParseErrorException;

class LargeIntegerTest extends TestCase
{
    private const POW_2_1024 =
        '1797693134862315907729305190789024733617976978942306572734300811577326758055009631' .
        '3270847732240753602112011387987139335765878976881441662249284743063947412437776789' .
        '3424865485276302219601246094119453082952085005768838150682342462881473913110540827' .
        '237163350510684586298239947245938479716304835356329624224137216';

    public function testEncodeLargeInteger()
    {
        $largeInt = gmp_pow(2, 1024);
        $expected = 'i' . self::POW_2_1024 . 'e';

        $encoded = Bencode::encode($largeInt);

        $this->assertEquals($expected, $encoded);
    }

    public function testDecodeLargeIntegerNoGMP()
    {
        $encoded = 'i' . self::POW_2_1024 . 'e';

        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage("Invalid integer format or integer overflow: '" . self::POW_2_1024 . "'");
        Bencode::decode($encoded);
    }

    public function testDecodeLargeIntegerGMP()
    {
        $encoded = 'i' . self::POW_2_1024 . 'e';
        $expected = gmp_pow(2, 1024);

        $decoded = Bencode::decode($encoded, useGMP: true);
        $this->assertInstanceOf(GMP::class, $decoded);
        $this->assertEquals($expected, $decoded);
    }
}
