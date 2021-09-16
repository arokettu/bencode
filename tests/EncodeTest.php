<?php

declare(strict_types=1);

// phpcs:ignoreFile Generic.Functions.FunctionCallArgumentSpacing.TooMuchSpaceAfterComma

namespace SandFox\Bencode\Tests;

use ArrayObject;
use PHPUnit\Framework\TestCase;
use SandFox\Bencode\Bencode;
use SandFox\Bencode\Exceptions\InvalidArgumentException;
use SandFox\Bencode\Types\BencodeSerializable;
use SandFox\Bencode\Types\DictType;
use SandFox\Bencode\Types\ListType;
use stdClass;

class EncodeTest extends TestCase
{
    public function testInteger()
    {
        // positive

        self::assertEquals('i314e', Bencode::encode(314));

        // negative

        self::assertEquals('i-512e', Bencode::encode(-512));

        // zero

        self::assertEquals('i0e', Bencode::encode(0));

        // scalars converted to integer

        self::assertEquals('i1e', Bencode::encode(true));
    }

    public function testString()
    {
        // arbitrary

        self::assertEquals('11:test string', Bencode::encode('test string'));

        // special characters

        self::assertEquals("25:zero\0newline\nsymblol05\x05ok", Bencode::encode("zero\0newline\nsymblol05\x05ok"));

        // empty

        self::assertEquals('0:', Bencode::encode(''));

        // unicode. prefix number reflects the number if bytes

        self::assertEquals('9:日本語', Bencode::encode('日本語'));

        // scalars converted to string

        self::assertEquals('6:3.1416', Bencode::encode(3.1416));

        // object with __toString

        self::assertEquals('6:string', Bencode::encode(new class {
            public function __toString()
            {
                return 'string';
            }
        }));
    }

    public function testList()
    {
        // sequential array should become list

        self::assertEquals('li1ei2e1:34:testi5ee', Bencode::encode([1, 2, '3', 'test', 5]));

        // list type wrapped traversable should become list

        self::assertEquals('li1ei2e1:34:testi5ee', Bencode::encode(
            new ListType(
                new ArrayObject([1, 2, '3', 'test', 5])
            )
        ));

        // sequential or not, we ignore the keys for ListType
        self::assertEquals('li1ei2e1:34:testi5ee', Bencode::encode(
            new ListType(
                new ArrayObject([
                    'key1' => 1,
                    'key2' => 2,
                    'key0' => '3',
                    'qqqq' => 'test',
                    423112 => 5,
                ])
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

    public function testDictionary()
    {
        // array with string keys

        self::assertEquals(
            'd3:key5:value4:test8:whatevere',
            Bencode::encode(['key' => 'value', 'test' => 'whatever'])
        );

        // any non-sequential array

        self::assertEquals('d1:0i1e1:1i2e1:21:31:3i5e1:44:teste', Bencode::encode([1, 2, '3', 4 => 'test', 3 => 5]));

        // stdClass

        $std = new stdClass();

        $std->key   = 'value';
        $std->test  = 'whatever';

        self::assertEquals('d3:key5:value4:test8:whatevere', Bencode::encode($std));

        // traversable

        self::assertEquals('d3:key5:value4:test8:whatevere', Bencode::encode(
            new ArrayObject(['key' => 'value', 'test' => 'whatever'])
        ));
        self::assertEquals('d3:key5:value4:test8:whatevere', Bencode::encode(
            new DictType(new ArrayObject(['key' => 'value', 'test' => 'whatever']))
        ));

        // even sequential
        self::assertEquals('d1:0i1e1:1i2e1:21:31:34:test1:4i5ee', Bencode::encode(
            new ArrayObject([1, 2, '3', 'test', 5])
        ));

        // empty dict
        self::assertEquals('de', Bencode::encode(new ArrayObject()));
    }

    public function testDictKeys()
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

    public function testAllTypes()
    {
        // just so some data in combinations
        $data1 = [
            'integer'   => 1,           // 7:integeri1e
            'list'      => [
                1, 2, 3, 'test',
                ['list', 'in', 'list'], // l4:list2:in4:liste
                ['dict' => 'in list'],  // d4:dict7:in liste
            ],                          // 4:listli1ei2ei3e4:testl4:list2:in4:listed4:dict7:in listee
            'dict'      => [
                'int' => 123, 'list' => []
            ],                          // 4:dictd3:inti123e4:listlee
            'string'    => 'str',       // 6:string3:str
        ];
        $data2 = [
            'integer'   => 1,
            'string'    => 'str',
            'dict'      => ['list' => [], 'int' => 123],
            'list'      => [1, 2, 3, 'test', ['list', 'in', 'list'], ['dict' => 'in list']],
        ];

        $expected = 'd' .
            '4:dictd3:inti123e4:listlee' .
            '7:integeri1e' .
            '4:listli1ei2ei3e4:testl4:list2:in4:listed4:dict7:in listee' .
            '6:string3:str' .
            'e';

        $result1 = Bencode::encode($data1);
        $result2 = Bencode::encode($data2);

        self::assertEquals($expected, $result1);
        self::assertEquals($result1, $result2); // different order of dict keys should not change the result
    }

    public function testSerializable()
    {
        // test returning scalar
        $dataScalar = new class implements BencodeSerializable {
            public function bencodeSerialize()
            {
                return 'Test';
            }
        };

        // test returning object which is also serializable
        $dataRecursion = new class ($dataScalar) implements BencodeSerializable {
            private $data;

            public function __construct($data)
            {
                $this->data = $data;
            }

            public function bencodeSerialize()
            {
                return $this->data;
            }
        };

        // Test returning array
        $dataArray = new class implements BencodeSerializable {
            public function bencodeSerialize()
            {
                return [
                    1,
                    2,
                    3,
                ];
            }
        };

        self::assertEquals('4:Test', Bencode::encode($dataScalar));
        self::assertEquals('4:Test', Bencode::encode($dataRecursion));
        self::assertEquals('li1ei2ei3ee', Bencode::encode($dataArray));
    }

    public function testUnknownType()
    {
        // We can't serialize resources
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Bencode doesn't know how to serialize an instance of resource");

        Bencode::encode(fopen(__FILE__, 'r'));
    }

    public function testUnknownObject()
    {
        // We can't serialize non-stringable objects
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Bencode doesn't know how to serialize an instance of " . get_class($this));

        Bencode::encode($this);
    }

    public function testJsonSerializable()
    {
        // test returning scalar
        $dataScalar = new class implements \JsonSerializable {
            public function jsonSerialize()
            {
                return 'Test';
            }
        };

        // test returning object which is also serializable
        $dataRecursion = new class($dataScalar) implements \JsonSerializable {
            private $data;

            public function __construct($data)
            {
                $this->data = $data;
            }

            public function jsonSerialize()
            {
                return $this->data;
            }
        };

        // Test returning array
        $dataArray = new class implements \JsonSerializable {
            public function jsonSerialize()
            {
                return [
                    1,
                    2,
                    3,
                ];
            }
        };

        self::assertEquals('4:Test', Bencode::encode($dataScalar, ['useJsonSerializable' => true]));
        self::assertEquals('4:Test', Bencode::encode($dataRecursion, useJsonSerializable: true));
        self::assertEquals('li1ei2ei3ee', Bencode::encode($dataArray, useJsonSerializable: true));
    }

    public function testNoRepeatedKeys()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Dictionary contains repeated keys: 'key'");

        Bencode::encode(
            (function () {
                yield 'key' => 'value1';
                yield 'key' => 'value2';
            })()
        );
    }
}
