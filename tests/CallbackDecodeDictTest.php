<?php

declare(strict_types=1);

// phpcs:disable Generic.Functions.FunctionCallArgumentSpacing.TooMuchSpaceAfterComma
// phpcs:disable Generic.Files.LineLength.TooLong

namespace Arokettu\Bencode\Tests;

use Arokettu\Bencode\CallbackDecoder;
use Arokettu\Bencode\Exceptions\ParseErrorException;
use Arokettu\Bencode\Tests\Helpers\CallbackCombiner;
use PHPUnit\Framework\TestCase;

class CallbackDecodeDictTest extends TestCase
{
    public function testValid(): void
    {
        // simple
        self::assertEquals(['a' => 'b', 'c' => 'd'], CallbackCombiner::decode(new CallbackDecoder(), 'd1:a1:b1:c1:de'));
        // numeric keys
        // php converts numeric array keys to integers
        self::assertEquals([1 => 2, 3 => 4], CallbackCombiner::decode(new CallbackDecoder(), 'd1:1i2e1:3i4ee'));
        // empty
        self::assertEquals(null, CallbackCombiner::decode(new CallbackDecoder(), 'de'));
    }

    public function testKeyNotString(): void
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage('Non string key found in the dictionary');

        (new CallbackDecoder())->decode('di123ei321ee', fn () => null);
    }

    public function testKeysNotSorted(): void
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage("Invalid order of dictionary keys: 'aaa' after 'zzz'");

        (new CallbackDecoder())->decode('d3:zzz0:3:aaa0:e', fn () => null);
    }

    public function testIntegerKeysNotSortedAsStrings(): void
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage("Invalid order of dictionary keys: '11' after '2'");

        (new CallbackDecoder())->decode('d1:11:a1:21:c2:111:b2:221:de', fn () => null); // keys here are sorted as integers: 1, 2, 11, 22
    }

    public function testMissingLastValue(): void
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage("Dictionary key without corresponding value: 'c'");

        (new CallbackDecoder())->decode('d1:a1:b1:ce', fn () => null); // three elements: last key misses a value
    }

    public function testDuplicateKey(): void
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage(
            "Invalid order of dictionary keys: 'a' after 'a'"
        ); // this may be confusing but it catches this bug

        (new CallbackDecoder())->decode('d1:a1:b1:a1:de', fn () => null);
    }
}
