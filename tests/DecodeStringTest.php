<?php

declare(strict_types=1);

namespace SandFox\Bencode\Tests;

use PHPUnit\Framework\TestCase;
use SandFoxMe\Bencode\Bencode;
use SandFoxMe\Bencode\Exceptions\ParseErrorException;

class DecodeStringTest extends TestCase
{
    private function errorMsgLength($value)
    {
        return "Invalid string length value: '{$value}'";
    }

    public function testValid()
    {
        // simple string
        self::assertEquals('String', Bencode::decode('6:String'));
        // empty string
        self::assertEquals('', Bencode::decode('0:'));
        // special chars
        self::assertEquals("zero\0newline\nsymblol05\x05ok", Bencode::decode("25:zero\0newline\nsymblol05\x05ok"));
        // unicode
        self::assertEquals('日本語', Bencode::decode('9:日本語'));
    }

    public function testIncorrectLengthZeroPrefix()
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage($this->errorMsgLength('06'));

        Bencode::decode('06:String');
    }

    public function testIncorrectLengthNegative()
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage($this->errorMsgLength('-6'));

        Bencode::decode('-6:String');
    }

    public function testIncorrectLengthFloat()
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage($this->errorMsgLength('6.0'));

        Bencode::decode('6.0:String');
    }

    public function testIncorrectLengthNotNumeric()
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage($this->errorMsgLength('six'));

        Bencode::decode('six:String');
    }

    public function testUnexpectedEof()
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage('Unexpected end of file while processing string');

        Bencode::decode('10:String');
    }
}
