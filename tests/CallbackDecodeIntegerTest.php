<?php

declare(strict_types=1);

namespace Arokettu\Bencode\Tests;

use Arokettu\Bencode\CallbackDecoder;
use Arokettu\Bencode\Exceptions\ParseErrorException;
use Arokettu\Bencode\Tests\Helpers\CallbackCombiner;
use PHPUnit\Framework\TestCase;

class CallbackDecodeIntegerTest extends TestCase
{
    private function errorMsg(string $value): string
    {
        return "Invalid integer format: '{$value}'";
    }

    public function testValid(): void
    {
        // valid values
        self::assertEquals(213, CallbackCombiner::decode(new CallbackDecoder(), 'i213e'));
        self::assertEquals(-314, CallbackCombiner::decode(new CallbackDecoder(), 'i-314e'));
        self::assertEquals(0, CallbackCombiner::decode(new CallbackDecoder(), 'i0e'));
    }

    public function testEmptyValue(): void
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage($this->errorMsg(''));

        (new CallbackDecoder())->decode('ie', fn () => null);
    }

    public function testUnfinished(): void
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage('Unexpected end of file while processing integer');

        (new CallbackDecoder())->decode('i', fn () => null);
    }

    public function testLeadingZero(): void
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage($this->errorMsg('013'));

        (new CallbackDecoder())->decode('i013e', fn () => null);
    }

    public function testLeadingZeroNegative(): void
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage($this->errorMsg('-013'));

        (new CallbackDecoder())->decode('i-013e', fn () => null);
    }

    public function testMinusZero(): void
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage($this->errorMsg('-0'));

        (new CallbackDecoder())->decode('i-0e', fn () => null);
    }

    public function testFloat(): void
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage($this->errorMsg('2.71828'));

        (new CallbackDecoder())->decode('i2.71828e', fn () => null);
    }

    public function testString(): void
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage($this->errorMsg('ffafw'));

        (new CallbackDecoder())->decode('iffafwe', fn () => null);
    }

    public function testOverflow(): void
    {
        $this->expectException(ParseErrorException::class);

        $value      = PHP_INT_MAX . '0000'; // PHP_INT_MAX * 10000
        $encoded    = "i{$value}e";

        $this->expectExceptionMessage("Integer overflow: '{$value}'");

        (new CallbackDecoder())->decode($encoded, fn () => null);
    }
}
