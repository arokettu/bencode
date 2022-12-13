<?php

declare(strict_types=1);

// phpcs:disable Generic.Functions.FunctionCallArgumentSpacing.TooMuchSpaceAfterComma
// phpcs:disable Generic.Files.LineLength.TooLong

namespace SandFox\Bencode\Tests;

use ArrayObject;
use PHPUnit\Framework\TestCase;
use SandFox\Bencode\Bencode;
use SandFox\Bencode\Exceptions\InvalidArgumentException;
use SandFox\Bencode\Exceptions\ParseErrorException;
use SandFox\Bencode\Types\DictType;
use stdClass;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;

class DecodeDictTest extends TestCase
{
    use ExpectDeprecationTrait;

    public function testValid(): void
    {
        // simple
        self::assertEquals(['a' => 'b', 'c' => 'd'], Bencode::decode('d1:a1:b1:c1:de'));
        // numeric keys
        // php converts numeric array keys to integers
        self::assertEquals([1 => 2, 3 => 4], Bencode::decode('d1:1i2e1:3i4ee'));
        // empty
        self::assertEquals([], Bencode::decode('de'));
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
        $decodedCallback = Bencode::decode($encoded, dictType: function ($array) use ($dict) {
            $this->assertEquals($dict, $array); // check thar array is passed here

            // you can pass extra parameter to the constructor for example
            return new ArrayObject($dict, ArrayObject::ARRAY_AS_PROPS);
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

    /**
     * @group legacy
     */
    public function testDeprecatedDictTypes(): void
    {
        $this->expectDeprecation(
            'Since arokettu/bencode 3.1.0: Passing class names to listType, dictType, and bigInt is deprecated, use closures instead'
        );

        $dict = [
            'k1' => 2,
            'k2' => 's1',
            'k3' => 3,
            'k4' => 's2',
            'k5' => 5,
        ];
        $encoded = 'd2:k1i2e2:k22:s12:k3i3e2:k42:s22:k5i5ee';

        // custom class
        $arrayObject = new ArrayObject($dict);
        $decodedAO = Bencode::decode($encoded, dictType: ArrayObject::class);

        $this->assertEquals(ArrayObject::class, $decodedAO::class);
        $this->assertEquals($arrayObject, $decodedAO);
    }

    public function testIncorrectType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            '$dictType must be Bencode\Collection enum value, class name, or callback'
        );

        Bencode::decode('de', dictType: "\0NonExistentClass");
    }
}
