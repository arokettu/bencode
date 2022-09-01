<?php

declare(strict_types=1);

namespace SandFox\Bencode\Tests;

use PHPUnit\Framework\TestCase;
use SandFox\Bencode\Bencode;
use SandFox\Bencode\Exceptions\ParseErrorException;

class DecodeIntegerTest extends TestCase
{
    private function errorMsg(string $value): string
    {
        return "Invalid integer format: '{$value}'";
    }

    public function testValid(): void
    {
        // valid values
        self::assertEquals(213, Bencode::decode('i213e'));
        self::assertEquals(-314, Bencode::decode('i-314e'));
        self::assertEquals(0, Bencode::decode('i0e'));
    }

    public function testEmptyValue(): void
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage($this->errorMsg(''));

        Bencode::decode('ie');
    }

    public function testUnfinished(): void
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage('Unexpected end of file while processing integer');

        Bencode::decode('i');
    }

    public function testLeadingZero(): void
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage($this->errorMsg('013'));

        Bencode::decode('i013e');
    }

    public function testLeadingZeroNegative(): void
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage($this->errorMsg('-013'));

        Bencode::decode('i-013e');
    }

    public function testMinusZero(): void
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage($this->errorMsg('-0'));

        Bencode::decode('i-0e');
    }

    public function testFloat(): void
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage($this->errorMsg('2.71828'));

        Bencode::decode('i2.71828e');
    }

    public function testString(): void
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage($this->errorMsg('ffafw'));

        Bencode::decode('iffafwe');
    }

    public function testOverflow(): void
    {
        $this->expectException(ParseErrorException::class);

        $value      = PHP_INT_MAX . '0000'; // PHP_INT_MAX * 10000
        $encoded    = "i{$value}e";

        $this->expectExceptionMessage("Integer overflow: '{$value}'");

        Bencode::decode($encoded);
    }
}
