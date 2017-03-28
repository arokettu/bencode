<?php

use PHPUnit\Framework\TestCase;
use SandFoxMe\Bencode\Bencode;
use SandFoxMe\Bencode\Exceptions\ParseErrorException;

/**
 * Class DecodeTest
 *
 * Testing some overall decoding features. Decoding exact types is tested in their own classes
 */
class DecodeTest extends TestCase
{
    public function testJunk()
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage('Probably some junk after the end of the file');

        Bencode::decode('i0ejunk');
    }

    public function testValidBencodeJunkIsAlsoJunk()
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage('Probably some junk after the end of the file');

        Bencode::decode('i0ele');
    }

    public function testNothing()
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage('Unexpected end of file');

        Bencode::decode('');
    }

    public function testRootIntegerNotFinalized()
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage('Unexpected end of file');

        Bencode::decode('i123');
    }

    public function testRootStringNotFinalized()
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage('Unexpected end of file');

        Bencode::decode('10:abc');
    }

    public function testRootStringDeclNotFinalized()
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage('Unexpected end of file');

        Bencode::decode('10');
    }

    public function testRootListNotFinalized()
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage('Unexpected end of file');

        Bencode::decode('li213ei123e');
    }

    public function testRootDictionaryNotFinalized()
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage('Unexpected end of file');

        Bencode::decode('d4:key1i1e4:key2i2e');
    }
}
