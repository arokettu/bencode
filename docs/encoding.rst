Encoding
########

.. note:: Parameter order is not guaranteed for options, use named parameters

Scalars and arrays
==================

.. warning:: *Possibly breaking change in 1.4 and 2.4:*

    Before 1.4 and 2.4 ``null`` was encoded as empty string and ``false`` was encoded as 0.
    Since bencode spec doesn't have bool and null values, it is not considered a bc break.
    Judging by info[private] behavior in BitTorrent spec, the old behavior could be considered as a bug.

.. code-block:: php

    <?php

    use SandFox\Bencode\Bencode;

    // non sequential array will become a dictionary
    $encoded = Bencode::encode([
        // sequential array will become a list
        'arr' => [1,2,3,4],
        // integer is stored as is
        'int' => 123,
        // float will become a string
        'float' => 3.1415,
        // true will be an integer 1
        'true' => true,
        // false and null values will be skipped
        'false' => false,
        // string can contain any binary data
        'string' => "test\0test",
    ]);
    // "d" .
    // "3:arr" . "l" . "i1e" . "i2e" . "i3e" . "i4e" . "e" .
    // "5:float" . "6:3.1415" .
    // "3:int" . "i123e" .
    // "6:string" . "9:test\0test" .
    // "4:true" . "i1e" .
    // "e"

Objects
=======

Traversable and stdClass
------------------------

Traversable and stdClass become dictionaries:

.. code-block:: php

    <?php

    use SandFox\Bencode\Bencode;

    $encoded = Bencode::encode(
        new ArrayObject([1,2,3])
    ); // "d1:0i1e1:1i2e1:2i3ee"

    $std = new stdClass();
    $std->a = '123';
    $std->b = 456;
    $encoded = Bencode::encode($std);
    // "d1:a3:1231:bi456ee"

Big integer support
-------------------

.. note:: More in the :ref:`decoding section <bencode_decoding_bigint>`

GMP object, Pear's Math_BigInteger, brick/math BigInteger, and internal type BigIntType (simple numeric string wrapper)
will become integers:

.. code-block:: php

    <?php

    use Brick\Math\BigInteger;
    use SandFox\Bencode\Bencode;
    use SandFox\Bencode\Types\BigIntType;

    $encoded = Bencode::encode([
        'gmp' => gmp_pow(2, 96),
        'brick' => BigInteger::of(2)->power(96),
        'pear' => (new Math_BigInteger(1))->bitwise_leftShift(96),
        'internal' => new BigIntType('7922816251426433759354395033'),
    ]); // "d5:bricki79228162514264337593543950336e3:gmpi792..."

Stringable
----------

Stringable objects are cast to strings:

.. code-block:: php

    <?php

    use SandFox\Bencode\Bencode;

    class ToString
    {
        public function __toString()
        {
            return 'I am string';
        }
    }

    $encoded = Bencode::encode(
        new ToString(),
    ); // "11:I am string"

Object Wrappers
---------------

You can use any traversable as a list by wrapping it with ``ListType``.
Keys will be discarded in that case.

.. code-block:: php

    <?php

    use SandFox\Bencode\Bencode;
    use SandFox\Bencode\Types\ListType;

    $encoded = Bencode::encode(
        new ListType(new ArrayObject([1,2,3]))
    ); // "li1ei2ei3ee"

You can use any traversable as a dictionary by wrapping it with ``DictType``.
Keys will be cast to string and must be unique.

.. note:: ``DictType`` is added for future compatibility with 3.x and is a noop in 1.x/2.x.

.. code-block:: php

    <?php

    use SandFox\Bencode\Bencode;
    use SandFox\Bencode\Types\DictType;

    $encoded = Bencode::encode(new DictType(
        (function () {
            yield 'key1' => 'value1';
            yield 'key2' => 'value2';
        })()
    )); // "d4:key16:value14:key26:value2e"

BencodeSerializable
-------------------

You can also force object representation by implementing BencodeSerializable interface.
This will work exactly like JsonSerializable_ interface.

.. code-block:: php

    <?php

    use SandFox\Bencode\Bencode;
    use SandFox\Bencode\Types\BencodeSerializable;

    class MyFile implements BencodeSerializable
    {
        public function bencodeSerialize()
        {
            return [
                'class' => static::class,
                'name'  => 'myfile.torrent',
                'size'  => 5 * 1024 * 1024,
            ];
        }
    }

    $file = new MyFile;

    $encoded = Bencode::encode($file);
    // "d5:class6:MyFile4:name14:myfile.torrent4:sizei5242880ee"

Optionally you can use JsonSerializable_ itself too:

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

    $encoded = Bencode::encode(
        $file,
        useJsonSerializable: true,
    ); // "d5:class6:MyFile4:name14:myfile.torrent4:sizei5242880ee"

Working with files
==================

Save data to file:

.. code-block:: php

    <?php

    use SandFox\Bencode\Bencode;

    Bencode::dump('testfile.torrent', $data);

Working with streams
====================

Save data to a writable stream or to a new php://temp if no stream is specified

.. code-block:: php

    <?php

    use SandFox\Bencode\Bencode;

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
        useJsonSerializable: true,
    );
    // is equivalent to
    $data = Bencode::encode("...", [
        'useJsonSerializable' => true,
    ]);

Encoder object
==============

Encoder object can be configured on creation and used multiple times.

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
