Encoding
########

.. note:: Parameter order is not guaranteed for options, use named parameters

Scalars and arrays
==================

.. code-block:: php

    <?php

    use SandFox\Bencode\Bencode;

    $encoded = Bencode::encode([    // array will become dictionary
        'arr'       => [1,2,3,4],       // sequential array will become a list
        'int'       => 123,             // integer is stored as is
        'float'     => 3.1415,          // float will become a string
        'true'      => true,            // true will be an integer 1
        'false'     => false,           // false and null values will be skipped
        'string'    => "test\0test",    // string can contain any binary data
    ]); // "d3:arrli1ei2ei3ei4ee5:float6:3.14153:inti123e6:string9:test\0test4:truei1ee"

Objects
=======

.. code-block:: php

    <?php

    use SandFox\Bencode\Bencode;
    use SandFox\Bencode\Types\DictType;
    use SandFox\Bencode\Types\ListType;

    // ArrayObject and stdClass become dictionaries
    $encoded = Bencode::encode(new ArrayObject([1,2,3])); // "d1:0i1e1:1i2e1:2i3ee"
    $std = new stdClass();
    $std->a = '123';
    $std->b = 456;
    $encoded = Bencode::encode($std); // "d1:a3:1231:bi456ee"

    // you can use any traversable as a list by wrapping it with ListType
    // keys will be discarded in that case
    $encoded = Bencode::encode(new ListType(new ArrayObject([1,2,3]))); // "li1ei2ei3ee"

    // you can use any traversable as a dictionary by wrapping it with DictType
    // keys will be cast to string and must be unique
    $encoded = Bencode::encode(new DictType(
        (function () {
            yield 'key1' => 'value1';
            yield 'key2' => 'value2';
        })()
    )); // "d4:key16:value14:key26:value2e"

    // optionally you can convert Stringable objects to strings
    class ToString
    {
        public function __toString()
        {
            return 'I am string';
        }
    }

    $encoded = Bencode::encode(new ToString(), useStringable: true); // "11:I am string"

    // GMP object, Pear's Math_BigInteger, brick/math,
    // and internal type BigIntType (simple numeric string wrapper)
    // become integer
    use SandFox\Bencode\Types\BigIntType;
    $encoded = Bencode::encode([
        'gmp' => gmp_pow(2, 96),
        'brick' => \Brick\Math\BigInteger::of(2)->power(96),
        'internal' => new BigIntType('7922816251426433759354395033'),
    ]); // "d5:bricki79228162514264337593543950336e3:gmpi792..."

BencodeSerializable
===================

You can also force object representation by implementing BencodeSerializable interface.
This will work exactly like JsonSerializable_ interface.

.. code-block:: php

    <?php

    use SandFox\Bencode\Bencode;
    use SandFox\Bencode\Types\BencodeSerializable;

    class MyFile implements BencodeSerializable
    {
        public function bencodeSerialize(): mixed
        {
            return [
                'class' => static::class,
                'name'  => 'myfile.torrent',
                'size'  => 5 * 1024 * 1024,
            ];
        }
    }

    $file = new MyFile;

    $encoded = Bencode::encode($file); // "d5:class6:MyFile4:name14:myfile.torrent4:sizei5242880ee"

Optionally you can use JsonSerializable itself too:

.. code-block:: php

    <?php

    use SandFox\Bencode\Bencode;

    class MyFile implements JsonSerializable
    {
        public function jsonSerialize()
        {
            return [
                'class' => static::class,
                'name'  => 'myfile.torrent',
                'size'  => 5 * 1024 * 1024,
            ];
        }
    }

    $file = new MyFile;

    // "d5:class6:MyFile4:name14:myfile.torrent4:sizei5242880ee"
    $encoded = Bencode::encode($file, useJsonSerializable: true);

Working with files
==================

.. code-block:: php

    <?php

    use SandFox\Bencode\Bencode;

    // save data to a bencoded file
    Bencode::dump('testfile.torrent', $data);

Working with streams
====================

.. code-block:: php

    <?php

    use SandFox\Bencode\Bencode;

    // save data to a bencoded writable stream or to a new php://temp if no stream is specified
    Bencode::encodeToStream($data, fopen('...', 'w'));

Options Array
=============

You can still use 1.x style options array instead of named params.
This parameter is kept for compatibility with 1.x calls.

.. code-block:: php

    <?php

    use SandFox\Bencode\Bencode;

    $data = Bencode::encode(
        "...",
        useStringable: true,
        useJsonSerializable: true,
    );
    // is equivalent to
    $data = Bencode::encode("...", [
        'useStringable' => true,
        'useJsonSerializable' => true,
    ]);

Encoder object
==============

3.0 added Encoder and Decoder objects that can be configured first.

.. code-block:: php

    <?php

    use SandFox\Bencode\Bencode;
    use SandFox\Bencode\Encoder;

    $encoder = new Encoder(useStringable: true);
    // all calls available:
    $encoder->encode($data);
    $encoder->encodeToStream($data, $stream);
    $encoder->dump($data, $filename);

.. _JsonSerializable:   http://php.net/manual/en/class.jsonserializable.php
