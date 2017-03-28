<?php

use PHPUnit\Framework\TestCase;
use SandFoxMe\Bencode\Bencode;
use SandFoxMe\Bencode\Exceptions\ParseErrorException;

class DecodeDictTest extends TestCase
{
    public function testValid()
    {
        // simple
        $this->assertEquals(['a' => 'b', 'c' => 'd'], Bencode::decode('d1:a1:b1:c1:de'));
        // numeric keys
        // php converts numeric array keys to integers
        $this->assertEquals([1 => 2, 3 => 4], Bencode::decode('d1:1i2e1:3i4ee'));
        // empty
        $this->assertEquals([], Bencode::decode('de'));
    }

    public function testKeyNotString()
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage('Non string key found in the dictionary');

        Bencode::decode('di123ei321ee');
    }

    public function testKeysNotSorted()
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage("Invalid order of dictionary keys: 'aaa' after 'zzz'");

        Bencode::decode('d3:zzz0:3:aaa0:e');
    }

    public function testIntegerKeysNotSortedAsStrings()
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage("Invalid order of dictionary keys: '11' after '2'");

        Bencode::decode('d1:11:a1:21:c2:111:b2:221:de'); // keys here are sorted as integers: 1, 2, 11, 22
    }

    public function testMissingLastValue()
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage("Dictionary key without corresponding value: 'c'");

        Bencode::decode('d1:a1:b1:ce'); // three elements: last key misses a value
    }

    public function testDuplicateKey()
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage("Invalid order of dictionary keys: 'a' after 'a'"); // this may be confusing but it catches this bug

        Bencode::decode('d1:a1:b1:a1:de');
    }
}
