<?php

use PHPUnit\Framework\TestCase;
use SandFoxMe\Bencode\Bencode;
use SandFoxMe\Bencode\Exceptions\InvalidArgumentException;

class DecodeListTest extends TestCase
{
    public function testListSimple()
    {
        // of integers
        $this->assertEquals([2, 3, 5, 7, 11, 13], Bencode::decode('li2ei3ei5ei7ei11ei13ee'));
        // of strings
        $this->assertEquals(['s1', 's2'], Bencode::decode('l2:s12:s2e'));
        // mixed
        $this->assertEquals([2, 's1', 3, 's2', 5], Bencode::decode('li2e2:s1i3e2:s2i5ee'));
        // empty
        $this->assertEquals([], Bencode::decode('le'));
    }

    public function testListTypes()
    {
        $list       = [2, 's1', 3, 's2', 5];
        $encoded    = 'li2e2:s1i3e2:s2i5ee';

        // array
        $decodedArray = Bencode::decode($encoded, ['listType' => 'array']);

        $this->assertTrue(is_array($decodedArray));
        $this->assertEquals($list, $decodedArray);

        // stdClass
        $object = (object)$list;
        $decodedObject = Bencode::decode($encoded, ['listType' => 'object']);

        $this->assertEquals(stdClass::class, get_class($decodedObject));
        $this->assertEquals($object, $decodedObject);

        // custom class
        $arrayObject = new ArrayObject($list);
        $decodedAO = Bencode::decode($encoded, ['listType' => ArrayObject::class]);

        $this->assertEquals(ArrayObject::class, get_class($decodedAO));
        $this->assertEquals($arrayObject, $decodedAO);

        // callback
        // use same array object as above
        $decodedCallback = Bencode::decode($encoded, ['listType' => function($array) use($list) {
            $this->assertEquals($list, $array); // check thar array is passed here

            // you can pass extra parameter to the constructor for example
            return new ArrayObject($list, ArrayObject::ARRAY_AS_PROPS);
        }]);

        $this->assertEquals(ArrayObject::class, get_class($decodedCallback));
        $this->assertEquals($arrayObject, $decodedCallback);
    }

    public function testIncorrectType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid type option for 'listType'. Type should be 'array', 'object', class name, or callback");

        Bencode::decode('le', ['listType' => "\0NonExistentClass"]);
    }
}
