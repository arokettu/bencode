<?php

declare(strict_types=1);

// phpcs:disable Generic.Functions.FunctionCallArgumentSpacing.TooMuchSpaceAfterComma
// phpcs:disable Generic.Files.LineLength.TooLong

namespace Arokettu\Bencode\Tests;

use Arokettu\Bencode\Bencode;
use Arokettu\Bencode\Exceptions\InvalidArgumentException;
use Arokettu\Bencode\Exceptions\ParseErrorException;
use Arokettu\Bencode\Types\DictType;
use ArrayObject;
use PHPUnit\Framework\TestCase;
use stdClass;

class DecodeDictTest extends TestCase
{
    public function testValid(): void
    {
        // simple
        self::assertEquals(new ArrayObject(['a' => 'b', 'c' => 'd']), Bencode::decode('d1:a1:b1:c1:de'));
        // numeric keys
        // php converts numeric array keys to integers
        self::assertEquals(new ArrayObject([1 => 2, 3 => 4]), Bencode::decode('d1:1i2e1:3i4ee'));
        // empty
        self::assertEquals(new ArrayObject(), Bencode::decode('de'));
    }

    public function testKeyNotString(): void
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage('Non string key found in the dictionary');

        Bencode::decode('di123ei321ee');
    }

    public function testKeysNotSorted(): void
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage("Invalid order of dictionary keys: 'aaa' after 'zzz'");

        Bencode::decode('d3:zzz0:3:aaa0:e');
    }

    public function testIntegerKeysNotSortedAsStrings(): void
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage("Invalid order of dictionary keys: '11' after '2'");

        Bencode::decode('d1:11:a1:21:c2:111:b2:221:de'); // keys here are sorted as integers: 1, 2, 11, 22
    }

    public function testMissingLastValue(): void
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage("Dictionary key without corresponding value: 'c'");

        Bencode::decode('d1:a1:b1:ce'); // three elements: last key misses a value
    }

    public function testDuplicateKey(): void
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage(
            "Invalid order of dictionary keys: 'a' after 'a'"
        ); // this may be confusing but it catches this bug

        Bencode::decode('d1:a1:b1:a1:de');
    }

    public function testDictTypes(): void
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
        $decodedArray = Bencode::decode($encoded, dictType: Bencode\Collection::ARRAY);

        $this->assertTrue(\is_array($decodedArray));
        $this->assertEquals($dict, $decodedArray);

        // stdClass
        $object = (object)$dict;
        $decodedObject = Bencode::decode($encoded, dictType: Bencode\Collection::OBJECT);

        $this->assertEquals(stdClass::class, $decodedObject::class);
        $this->assertEquals($object, $decodedObject);

        // callback
        $arrayObject = new ArrayObject($dict);
        $decodedCallback = Bencode::decode($encoded, dictType: function ($decoded) use ($dict) {
            $array = [...$decoded];
            self::assertIsIterable($decoded); // check that iterable is passed here
            self::assertEquals($dict, $array); // check content

            // you can pass extra parameter to the constructor for example
            return new ArrayObject($array, ArrayObject::ARRAY_AS_PROPS);
        });

        $this->assertEquals(ArrayObject::class, $decodedCallback::class);
        $this->assertEquals($arrayObject, $decodedCallback);
    }

    public function testNoRepeatedKeys(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Dictionary contains repeated keys: 'key'");

        Bencode::encode(
            new DictType((function () {
                yield 'key' => 'value1';
                yield 'key' => 'value2';
            })())
        );
    }
}
