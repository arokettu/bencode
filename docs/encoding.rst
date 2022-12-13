Encoding
########

.. versionchanged:: 2.0 options array is replaced with named parameters
.. note:: Parameter order is not guaranteed for options, use named parameters

Scalars and arrays
==================

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

ArrayObject and stdClass
------------------------

.. versionchanged:: 3.0 ``Traversable`` objects no longer become dictionaries automatically

ArrayObject and stdClass become dictionaries:

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

.. versionadded:: 1.5/2.5 GMP support
.. versionadded:: 1.6/2.6 Pear's Math_BigInteger, brick/math, BigIntType support

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

.. versionchanged:: 3.0 ``Stringable`` objects no longer become strings automatically

You can convert ``Stringable`` objects to strings using ``useStringable`` option:

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
        useStringable: true,
    ); // "11:I am string"

Object Wrappers
---------------

.. versionadded:: 1.7/2.7/3.0 ``DictType``

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

.. versionadded:: 1.2
.. versionadded:: 1.7/2.7/3.0 ``JsonSerializable`` handling

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

.. versionchanged:: 3.0 ``($filename, $data)`` → ``($data, $filename)``

Save data to file:

.. code-block:: php

    <?php

    use SandFox\Bencode\Bencode;

    Bencode::dump($data, 'testfile.torrent');

Working with streams
====================

.. versionadded:: 1.5/2.5

Save data to a writable stream or to a new php://temp if no stream is specified

.. code-block:: php

    <?php

    use SandFox\Bencode\Bencode;

    Bencode::encodeToStream($data, fopen('...', 'w'));

Options Array
=============

.. deprecated:: 3.1

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

.. versionadded:: 1.7/2.7/3.0

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
