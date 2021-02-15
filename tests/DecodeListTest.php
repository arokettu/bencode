<?php

declare(strict_types=1);

// phpcs:ignoreFile Generic.Functions.FunctionCallArgumentSpacing.TooMuchSpaceAfterComma

namespace SandFox\Bencode\Tests;

use ArrayObject;
use PHPUnit\Framework\TestCase;
use SandFoxMe\Bencode\Bencode;
use SandFoxMe\Bencode\Exceptions\InvalidArgumentException;
use stdClass;

class DecodeListTest extends TestCase
{
    public function testListSimple()
    {
        // of integers
        self::assertEquals([2, 3, 5, 7, 11, 13], Bencode::decode('li2ei3ei5ei7ei11ei13ee'));
        // of strings
        self::assertEquals(['s1', 's2'], Bencode::decode('l2:s12:s2e'));
        // mixed
        self::assertEquals([2, 's1', 3, 's2', 5], Bencode::decode('li2e2:s1i3e2:s2i5ee'));
        // empty
        self::assertEquals([], Bencode::decode('le'));
    }

    public function testListTypes()
    {
        $list       = [2, 's1', 3, 's2', 5];
        $encoded    = 'li2e2:s1i3e2:s2i5ee';

        // array
        $decodedArray = Bencode::decode($encoded, ['listType' => 'array']);

        self::assertTrue(is_array($decodedArray));
        self::assertEquals($list, $decodedArray);

        // stdClass
        $object = (object)$list;
        $decodedObject = Bencode::decode($encoded, ['listType' => 'object']);

        self::assertEquals(stdClass::class, get_class($decodedObject));
        self::assertEquals($object, $decodedObject);

        // custom class
        $arrayObject = new ArrayObject($list);
        $decodedAO = Bencode::decode($encoded, ['listType' => ArrayObject::class]);

        self::assertEquals(ArrayObject::class, get_class($decodedAO));
        self::assertEquals($arrayObject, $decodedAO);

        // callback
        // use same array object as above
        $decodedCallback = Bencode::decode($encoded, ['listType' => function ($array) use($list) {
            self::assertEquals($list, $array); // check thar array is passed here

            // you can pass extra parameter to the constructor for example
            return new ArrayObject($list, ArrayObject::ARRAY_AS_PROPS);
        }]);

        self::assertEquals(ArrayObject::class, get_class($decodedCallback));
        self::assertEquals($arrayObject, $decodedCallback);
    }

    public function testIncorrectType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid type option for 'listType'. Type should be 'array', 'object', class name, or callback");

        Bencode::decode('le', ['listType' => "\0NonExistentClass"]);
    }
}
