<?php

declare(strict_types=1);

namespace Arokettu\Bencode\Tests;

use Arokettu\Bencode\CallbackDecoder;
use Arokettu\Bencode\Exceptions\ParseErrorException;
use PHPUnit\Framework\TestCase;

/**
 * Class DecodeTest
 *
 * Testing some overall decoding features. Decoding exact types is tested in their own classes
 */
final class CallbackDecodeTest extends TestCase
{
    public function testJunk(): void
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage('Probably some junk after the end of the file');

        (new CallbackDecoder())->decode('i0ejunk', static fn () => null);
    }

    public function testValidBencodeJunkIsAlsoJunk(): void
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage('Probably some junk after the end of the file');

        (new CallbackDecoder())->decode('i0ele', static fn () => null);
    }

    public function testNothing(): void
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage('Unexpected end of file');

        (new CallbackDecoder())->decode('', static fn () => null);
    }

    public function testRootIntegerNotFinalized(): void
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage('Unexpected end of file');

        (new CallbackDecoder())->decode('i123', static fn () => null);
    }

    public function testRootStringNotFinalized(): void
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage('Unexpected end of file');

        (new CallbackDecoder())->decode('10:abc', static fn () => null);
    }

    public function testRootStringDeclNotFinalized(): void
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage('Unexpected end of file');

        (new CallbackDecoder())->decode('10', static fn () => null);
    }

    public function testRootListNotFinalized(): void
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage('Unexpected end of file');

        (new CallbackDecoder())->decode('li213ei123e', static fn () => null);
    }

    public function testRootDictionaryNotFinalized(): void
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage('Unexpected end of file');

        (new CallbackDecoder())->decode('d4:key1i1e4:key2i2e', static fn () => null);
    }
}
