<?php

use PHPUnit\Framework\TestCase;
use SandFoxMe\Bencode\Bencode;
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

        // scalars converted to string

        $this->assertEquals('6:3.1416', Bencode::encode(3.1416));
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
    }
}
