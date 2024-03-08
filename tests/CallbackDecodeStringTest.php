<?php

declare(strict_types=1);

namespace Arokettu\Bencode\Tests;

use Arokettu\Bencode\CallbackDecoder;
use Arokettu\Bencode\Exceptions\ParseErrorException;
use Arokettu\Bencode\Tests\Helpers\CallbackCombiner;
use PHPUnit\Framework\TestCase;

class CallbackDecodeStringTest extends TestCase
{
    private function errorMsgLength(string $value): string
    {
        return "Invalid string length value: '{$value}'";
    }

    public function testValid(): void
    {
        // simple string
        self::assertEquals('String', CallbackCombiner::parse(new CallbackDecoder(), '6:String'));
        // empty string
        self::assertEquals('', CallbackCombiner::parse(new CallbackDecoder(), '0:'));
        // special chars
        self::assertEquals("zero\0newline\nsymblol05\x05ok", CallbackCombiner::parse(
            new CallbackDecoder(),
            "25:zero\0newline\nsymblol05\x05ok"
        ));
        // unicode
        self::assertEquals('日本語', CallbackCombiner::parse(new CallbackDecoder(), '9:日本語'));
    }

    public function testIncorrectLengthZeroPrefix(): void
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage($this->errorMsgLength('06'));

        (new CallbackDecoder())->decode('06:String', fn () => null);
    }

    public function testIncorrectLengthNegative(): void
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage($this->errorMsgLength('-6'));

        (new CallbackDecoder())->decode('-6:String', fn () => null);
    }

    public function testIncorrectLengthFloat(): void
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage($this->errorMsgLength('6.0'));

        (new CallbackDecoder())->decode('6.0:String', fn () => null);
    }

    public function testIncorrectLengthNotNumeric(): void
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage($this->errorMsgLength('six'));

        (new CallbackDecoder())->decode('six:String', fn () => null);
    }

    public function testUnexpectedEof(): void
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage('Unexpected end of file while processing string');

        (new CallbackDecoder())->decode('10:String', fn () => null);
    }
}
