<?php

use PHPUnit\Framework\TestCase;
use SandFoxMe\Bencode\Bencode;
use SandFoxMe\Bencode\Types\BencodeSerializable;
use SandFoxMe\Bencode\Types\ListType;

class EncodeTest extends TestCase
{
    public function testInteger()
    {
        // positive

        $this->assertEquals('i314e', Bencode::encode(314));

        // negative

        $this->assertEquals('i-512e', Bencode::encode(-512));

        // zero

        $this->assertEquals('i0e', Bencode::encode(0));

        // scalars converted to integer

        $this->assertEquals('i1e', Bencode::encode(true));
        $this->assertEquals('i0e', Bencode::encode(false));
    }

    public function testString()
    {
        // arbitrary

        $this->assertEquals('11:test string', Bencode::encode('test string'));

        // special characters

        $this->assertEquals("25:zero\0newline\nsymblol05\x05ok", Bencode::encode("zero\0newline\nsymblol05\x05ok"));

        // empty

        $this->assertEquals('0:', Bencode::encode(''));

        // unicode. prefix number reflects the number if bytes

        $this->assertEquals('9:日本語', Bencode::encode('日本語'));

        // scalars converted to string

        $this->assertEquals('6:3.1416', Bencode::encode(3.1416));

        // object with __toString

        $this->assertEquals('6:string', Bencode::encode(new class {
            public function __toString()
            {
                return 'string';
            }
        }));
    }

    public function testList()
    {
        // sequential array should become list

        $this->assertEquals('li1ei2e1:34:testi5ee', Bencode::encode([1, 2, '3', 'test', 5]));

        // list type wrapped traversable should become list

        $this->assertEquals('li1ei2e1:34:testi5ee', Bencode::encode(
            new ListType(
                new ArrayObject([1, 2, '3', 'test', 5])
            )
        ));

        // sequential or not, we ignore the keys for ListType
        $this->assertEquals('li1ei2e1:34:testi5ee', Bencode::encode(
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

        $this->assertEquals('li1ei2e1:34:testi5ee', Bencode::encode(
            new ListType([
                'key1' => 1,
                'key2' => 2,
                'key0' => '3',
                'qqqq' => 'test',
                423112 => 5,
            ])
        ));

        // empty list

        $this->assertEquals('le', Bencode::encode([]));
    }

    public function testDictionary()
    {
        // array with string keys

        $this->assertEquals('d3:key5:value4:test8:whatevere', Bencode::encode(['key' => 'value', 'test' => 'whatever']));

        // any non-sequential array

        $this->assertEquals('d1:0i1e1:1i2e1:21:31:3i5e1:44:teste', Bencode::encode([1, 2, '3', 4 => 'test', 3 => 5]));

        // stdClass

        $std = new stdClass();

        $std->key   = 'value';
        $std->test  = 'whatever';

        $this->assertEquals('d3:key5:value4:test8:whatevere', Bencode::encode($std));

        // traversable

        $this->assertEquals('d3:key5:value4:test8:whatevere', Bencode::encode(
            new ArrayObject(['key' => 'value', 'test' => 'whatever'])
        ));

        // even sequential
        $this->assertEquals('d1:0i1e1:1i2e1:21:31:34:test1:4i5ee', Bencode::encode(
            new ArrayObject([1, 2, '3', 'test', 5])
        ));

        // empty dict
        $this->assertEquals('de', Bencode::encode(new ArrayObject()));
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

        $this->assertEquals($expectedWithStringKeys, Bencode::encode($stringKeys));

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

        $this->assertEquals($expectedWithNumericKeys, Bencode::encode($numericKeys));
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

        $expected = 'd4:dictd3:inti123e4:listlee7:integeri1e4:listli1ei2ei3e4:testl4:list2:in4:listed4:dict7:in listee6:string3:stre';

        $result1 = Bencode::encode($data1);
        $result2 = Bencode::encode($data2);

        $this->assertEquals($expected,  $result1);
        $this->assertEquals($result1,   $result2); // different order of dict keys should not change the result
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
        $dataRecursion = new class($dataScalar) implements BencodeSerializable {
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

        $this->assertEquals('4:Test',       Bencode::encode($dataScalar));
        $this->assertEquals('4:Test',       Bencode::encode($dataRecursion));
        $this->assertEquals('li1ei2ei3ee',  Bencode::encode($dataArray));
    }
}
