<?php

declare(strict_types=1);

namespace SandFox\Bencode\Tests;

use ArrayObject;
use PHPUnit\Framework\TestCase;
use SandFox\Bencode\Bencode;
use SandFox\Bencode\Exceptions\ParseErrorException;
use stdClass;

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
        $this->expectExceptionMessage(
            "Invalid order of dictionary keys: 'a' after 'a'"
        ); // this may be confusing but it catches this bug

        Bencode::decode('d1:a1:b1:a1:de');
    }

    public function testListTypes()
    {
        $dict = [
            'k1' => 2,
            'k2' => 's1',
            'k3' => 3,
            'k4' => 's2',
            'k5' => 5,
        ];
        $encoded = 'd2:k1i2e2:k22:s12:k3i3e2:k42:s22:k5i5ee';

        // array
        $decodedArray = Bencode::decode($encoded, dictType: 'array');

        $this->assertTrue(is_array($decodedArray));
        $this->assertEquals($dict, $decodedArray);

        // stdClass
        $object = (object)$dict;
        $decodedObject = Bencode::decode($encoded, dictType: 'object');

        $this->assertEquals(stdClass::class, get_class($decodedObject));
        $this->assertEquals($object, $decodedObject);

        // custom class
        $arrayObject = new ArrayObject($dict);
        $decodedAO = Bencode::decode($encoded, dictType: ArrayObject::class);

        $this->assertEquals(ArrayObject::class, get_class($decodedAO));
        $this->assertEquals($arrayObject, $decodedAO);

        // callback
        // use same array object as above
        $decodedCallback = Bencode::decode($encoded, dictType: function ($array) use ($dict) {
            $this->assertEquals($dict, $array); // check thar array is passed here

            // you can pass extra parameter to the constructor for example
            return new ArrayObject($dict, ArrayObject::ARRAY_AS_PROPS);
        });

        $this->assertEquals(ArrayObject::class, get_class($decodedCallback));
        $this->assertEquals($arrayObject, $decodedCallback);
    }
}
