<?php

declare(strict_types=1);

namespace SandFox\Bencode\Tests;

use PHPUnit\Framework\TestCase;
use SandFox\Bencode\Bencode;
use SandFox\Bencode\Types\ListType;

class EncodeListTest extends TestCase
{
    public function testList(): void
    {
        // sequential array should become list

        self::assertEquals('li1ei2e1:34:testi5ee', Bencode::encode([1, 2, '3', 'test', 5]));

        // list type wrapped traversable should become list

        self::assertEquals('li1ei2e1:34:testi5ee', Bencode::encode(
            new ListType(
                (function () {
                    yield from [1, 2, '3', 'test', 5];
                })()
            )
        ));

        // sequential or not, we ignore the keys for ListType
        self::assertEquals('li1ei2e1:34:testi5ee', Bencode::encode(
            new ListType(
                (function () {
                    yield 'key1' => 1;
                    yield 'key2' => 2;
                    yield 'key0' => '3';
                    yield 'qqqq' => 'test';
                    yield 423112 => 5;
                })()
            )
        ));

        // test list type consuming an array

        self::assertEquals('li1ei2e1:34:testi5ee', Bencode::encode(
            new ListType([
                'key1' => 1,
                'key2' => 2,
                'key0' => '3',
                'qqqq' => 'test',
                423112 => 5,
            ])
        ));

        // empty list

        self::assertEquals('le', Bencode::encode([]));
    }
}
