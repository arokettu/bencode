<?php

declare(strict_types=1);

namespace SandFox\Bencode\Tests;

use PHPUnit\Framework\TestCase;
use SandFox\Bencode\Bencode;
use SandFox\Bencode\Exceptions\InvalidArgumentException;
use SandFox\Bencode\Types\DictType;

class EncodeDictTest extends TestCase
{
    public function testDictionary(): void
    {
        // array with string keys
        self::assertEquals(
            'd3:key5:value4:test8:whatevere',
            Bencode::encode(['key' => 'value', 'test' => 'whatever'])
        );

        // any non-sequential array
        self::assertEquals('d1:0i1e1:1i2e1:21:31:3i5e1:44:teste', Bencode::encode([1, 2, '3', 4 => 'test', 3 => 5]));

        // stdClass
        $std = new \stdClass();

        $std->key   = 'value';
        $std->test  = 'whatever';

        self::assertEquals('d3:key5:value4:test8:whatevere', Bencode::encode($std));

        // ArrayObject
        self::assertEquals('d3:key5:value4:test8:whatevere', Bencode::encode(
            new \ArrayObject(['key' => 'value', 'test' => 'whatever'])
        ));

        // even sequential
        self::assertEquals('d1:0i1e1:1i2e1:21:31:34:test1:4i5ee', Bencode::encode(
            new \ArrayObject([1, 2, '3', 'test', 5])
        ));

        // empty dict
        self::assertEquals('de', Bencode::encode(new \ArrayObject()));

        // DictType
        self::assertEquals('d3:key5:value4:test8:whatevere', Bencode::encode(
            new DictType((function () {
                yield 'key' => 'value';
                yield 'test' => 'whatever';
            })())
        ));
    }

    public function testNoTraversables(): void
    {
        // no longer automatically decode traversables

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Bencode doesn't know how to serialize an instance of Generator");

        Bencode::encode(
            (function () {
                yield 'key' => 'value';
                yield 'test' => 'whatever';
            })()
        );
    }

    public function testDictKeys(): void
    {
        $stringKeys = [
            'a'     => '',
            'b'     => '',
            'c'     => '',
            'A'     => '',
            'B'     => '',
            'C'     => '',
            'key'   => '',
            '本'     => '',
            'ы'     => '',
            'Ы'     => '',
            'š'     => '',
            'Š'     => '',
        ];

        // keys should be sorted by binary comparison of the strings
        $expectedWithStringKeys = 'd' .
            '1:A0:' .
            '1:B0:' .
            '1:C0:' .
            '1:a0:' .
            '1:b0:' .
            '1:c0:' .
            '3:key0:' .
            '2:Š0:' .
            '2:š0:' .
            '2:Ы0:' .
            '2:ы0:' .
            '3:本0:' .
            'e';

        self::assertEquals($expectedWithStringKeys, Bencode::encode($stringKeys));

        // also check that php doesn't silently convert numeric keys to integer
        $numericKeys = [
            1 => '',
            5 => '',
            9 => '',
            11 => '',
            55 => '',
            99 => '',
            111 => '',
            555 => '',
            999 => '',
        ];

        $expectedWithNumericKeys = 'd' .
            '1:10:' .
            '2:110:' .
            '3:1110:' .
            '1:50:' .
            '2:550:' .
            '3:5550:' .
            '1:90:' .
            '2:990:' .
            '3:9990:' .
            'e';

        self::assertEquals($expectedWithNumericKeys, Bencode::encode($numericKeys));
    }
}
