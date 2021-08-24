<?php

declare(strict_types=1);

namespace SandFox\Bencode\Tests;

use PHPUnit\Framework\TestCase;
use SandFox\Bencode\Bencode;
use SandFox\Bencode\Exceptions\ParseErrorException;

/**
 * Class DecodeTest
 *
 * Testing some overall decoding features. Decoding exact types is tested in their own classes
 */
class DecodeTest extends TestCase
{
    public function testJunk(): void
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage('Probably some junk after the end of the file');

        Bencode::decode('i0ejunk');
    }

    public function testValidBencodeJunkIsAlsoJunk(): void
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage('Probably some junk after the end of the file');

        Bencode::decode('i0ele');
    }

    public function testNothing(): void
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage('Unexpected end of file');

        Bencode::decode('');
    }

    public function testRootIntegerNotFinalized(): void
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage('Unexpected end of file');

        Bencode::decode('i123');
    }

    public function testRootStringNotFinalized(): void
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage('Unexpected end of file');

        Bencode::decode('10:abc');
    }

    public function testRootStringDeclNotFinalized(): void
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage('Unexpected end of file');

        Bencode::decode('10');
    }

    public function testRootListNotFinalized(): void
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage('Unexpected end of file');

        Bencode::decode('li213ei123e');
    }

    public function testRootDictionaryNotFinalized(): void
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage('Unexpected end of file');

        Bencode::decode('d4:key1i1e4:key2i2e');
    }
}
