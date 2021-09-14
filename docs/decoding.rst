Decoding
########

.. note:: Parameter order is not guaranteed for options, use named parameters

The simplest case
=================

.. code-block:: php

    <?php

    use SandFox\Bencode\Bencode;

    // simple decoding, lists and dictionaries will be arrays
    $data = Bencode::decode("d3:arrli1ei2ei3ei4ee4:booli1e5:float6:3.14153:inti123e6:string9:test\0teste");
    // [
    //   "arr" => [1,2,3,4],
    //   "bool" => 1,
    //   "float" => "3.1415",
    //   "int" => 123,
    //   "string" => "test\0test",
    // ]

Lists and Dictionaries
======================

.. code-block:: php

    <?php

    use SandFox\Bencode\Bencode;

    // You can control lists and dictionaries types with options
    $data = Bencode::decode(
        "...",
        listType: Bencode\Collection::ARRAY,  // this is a default for both listType and dictType
        dictType: Bencode\Collection::OBJECT, // convert to stdClass
    );
    // advanced variants:
    $data = Bencode::decode(
        "...",
        dictType: ArrayObject::class, // pass class name, new $type($array) will be created
        listType: function ($array) { // or callback for greater flexibility
            return new ArrayObject($array, ArrayObject::ARRAY_AS_PROPS);
        },
    );

Large integers
==============

.. important::
    These math libraries are not explicit dependencies of this library.
    Install them separately before enabling.

By default the library only works with a native integer type but if you need to use large integers,
for example, if you want to parse a torrent file for a >= 4GB file on a 32 bit system,
you can enable big integer support.

.. code-block:: php

    <?php

    use SandFox\Bencode\Bencode;

    // GMP
    $data = Bencode::decode(
        "d3:inti79228162514264337593543950336ee",
        bigInt: Bencode\BigInt::GMP,
    ); // ['int' => gmp_init('79228162514264337593543950336')]

    // brick/math
    $data = Bencode::decode(
        "d3:inti79228162514264337593543950336ee",
        bigInt: Bencode\BigInt::BRICK_MATH,
    ); // ['int' => \Brick\Math\BigInteger::of('79228162514264337593543950336')]

    // Math_BigInteger from PEAR
    $data = Bencode::decode(
        "d3:inti79228162514264337593543950336ee",
        bigInt: Bencode\BigInt::PEAR,
    ); // ['int' => new \Math_BigInteger('79228162514264337593543950336')]

    // Internal BigIntType class
    // does not require any external dependencies but also does not allow any manipulation
    $data = Bencode::decode(
        "d3:inti79228162514264337593543950336ee",
        bigInt: Bencode\BigInt::INTERNAL,
    ); // ['int' => new \SandFox\Bencode\Types\BigIntType('79228162514264337593543950336')]
    // BigIntType is a value object with several getters:
    // simple string representation:
    $str = $data->getValue();
    // converters to the supported libraries:
    $obj = $data->toGMP();
    $obj = $data->toPear();
    $obj = $data->toBrickMath();

    // like listType and dictType you can use a callable or a class name
    $data = Bencode::decode(
        "d3:inti79228162514264337593543950336ee",
        bigInt: fn ($v) => $v,
    ); // ['int' => '79228162514264337593543950336']
    $data = Bencode::decode(
        "d3:inti79228162514264337593543950336ee",
        bigInt: MyBigIntHandler::class,
    ); // ['int' => new MyBigIntHandler('79228162514264337593543950336')]]

.. _GMP: https://www.php.net/manual/en/book.gmp.php
.. _brick/math: https://github.com/brick/math
.. _Math_BigInteger: https://pear.php.net/package/Math_BigInteger

Working with files
==================

.. code-block:: php

    <?php

    use SandFox\Bencode\Bencode;

    // load data from a bencoded file
    $data = Bencode::load('testfile.torrent');

Working with streams
====================

.. code-block:: php

    <?php

    use SandFox\Bencode\Bencode;

    // load data from a bencoded seekable readable stream
    $data = Bencode::decodeStream(fopen('...', 'r'));

Options Array
=============

You can still use 1.x style options array instead of named params.
This parameter is kept for compatibility with 1.x calls.

.. code-block:: php

    <?php

    use SandFox\Bencode\Bencode;

    $data = Bencode::decode(
        "...",
        listType: Bencode\Collection::ARRAY,
        dictType: Bencode\Collection::OBJECT,
        bigInt:   Bencode\BigInt::INTERNAL,
    );
    // is equivalent to
    $data = Bencode::decode("...", [
        'listType' => Bencode\Collection::ARRAY,
        'dictType' => Bencode\Collection::OBJECT,
        'bigInt' =>   Bencode\BigInt::INTERNAL,
    ]);

Decoder object
==============

3.0 added Encoder and Decoder objects that can be configured first.

.. code-block:: php

    <?php

    use SandFox\Bencode\Bencode;
    use SandFox\Bencode\Decoder;

    $decoder = new Decoder(bigInt: Bencode\BigInt::INTERNAL);
    // all calls available:
    $decoder->decode($encoded);
    $decoder->decodeStream($encoded, $stream);
    $decoder->load($filename);
