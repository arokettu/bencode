<?php

use PHPUnit\Framework\TestCase;
use SandFoxMe\Bencode\Bencode;

class LargeIntegerTest extends TestCase
{
    public function testEncodeLargeInteger()
    {
        $largeInt = gmp_pow(2, 96);
        $expected = 'i79228162514264337593543950336e';

        $encoded = Bencode::encode($largeInt);

        $this->assertEquals($expected, $encoded);
    }
}
