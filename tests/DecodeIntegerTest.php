<?php

use PHPUnit\Framework\TestCase;
use SandFoxMe\Bencode\Bencode;
use SandFoxMe\Bencode\Exceptions\ParseErrorException;

class DecodeIntegerTest extends TestCase
{
    private function errorMsg($value)
    {
        return "Invalid integer format or integer overflow: '{$value}'";
    }

    public function testValid()
    {
        // valid values
        $this->assertEquals(213, Bencode::decode('i213e'));
        $this->assertEquals(-314, Bencode::decode('i-314e'));
        $this->assertEquals(0, Bencode::decode('i0e'));
    }

    public function testEmptyValue()
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage($this->errorMsg(''));

        Bencode::decode('ie');
    }

    public function testLeadingZero()
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage($this->errorMsg('013'));

        Bencode::decode('i013e');
    }

    public function testLeadingZeroNegative()
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage($this->errorMsg('-013'));

        Bencode::decode('i-013e');
    }

    public function testMinusZero()
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage($this->errorMsg('-0'));

        Bencode::decode('i-0e');
    }

    public function testFloat()
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage($this->errorMsg('2.71828'));

        Bencode::decode('i2.71828e');
    }

    public function testString()
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage($this->errorMsg('ffafw'));

        Bencode::decode('iffafwe');
    }

    public function testOverflow()
    {
        $this->expectException(ParseErrorException::class);

        $value      = PHP_INT_MAX . '0000'; // PHP_INT_MAX * 10000
        $encoded    = "i{$value}e";

        $this->expectExceptionMessage($this->errorMsg($value));

        Bencode::decode($encoded);
    }
}
