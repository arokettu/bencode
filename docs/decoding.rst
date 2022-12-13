Decoding
########

.. versionchanged:: 2.0 options array is replaced with named parameters
.. note:: Parameter order is not guaranteed for options, use named parameters

Scalars
=======

Scalars will be converted to their respective types.

.. code-block:: php

    <?php

    use SandFox\Bencode\Bencode;

    $data = Bencode::decode(
        "d" .
        "3:arrli1ei2ei3ei4ee" .
        "4:booli1e" .
        "5:float6:3.1415" .
        "3:inti123e" .
        "6:string9:test\0test" .
        "e"
    );
    // [
    //   "arr" => [1,2,3,4],
    //   "bool" => 1,
    //   "float" => "3.1415",
    //   "int" => 123,
    //   "string" => "test\0test",
    // ]

Please note that floats and booleans will stay converted because Bencode has no native support for these types.

Lists and Dictionaries
======================

.. versionchanged:: 3.0 ``Collection`` is now a real enum
.. deprecated:: 3.1 Passing class names as handlers will be removed in 4.0

Dictionaries and lists will be arrays by default.
You can change this behavior with options.
Use ``Collection`` enum for built in behaviors:

.. code-block:: php

    <?php

    use SandFox\Bencode\Bencode;

    $data = Bencode::decode(
        "...",
        // this is a default for both listType and dictType
        listType: Bencode\Collection::ARRAY,
        // convert to ArrayObject
        dictType: Bencode\Collection::ARRAY_OBJECT,
        // or to stdClass
        // dictType: Bencode\Collection::STDCLASS,
    );

Or use advanced control with callbacks:

.. code-block:: php

    <?php

    use SandFox\Bencode\Bencode;

    $data = Bencode::decode(
        "...",
        // use callback for greater flexibility
        listType: function (array $array) {
            return new ArrayObject(
                $array,
                ArrayObject::ARRAY_AS_PROPS
            );
        },
    );

.. _bencode_decoding_bigint:

Big Integers
============

By default the library only works with a native integer type but if you need to use large integers,
for example, if you want to parse a torrent file for a >= 4GB file on a 32 bit system,
you can enable big integer support.

External Libraries
------------------

.. versionadded:: 1.5/2.5 GMP support
.. versionadded:: 1.6/2.6 Pear's Math_BigInteger, brick/math
.. versionchanged:: 3.0 ``BigInt`` is now a real enum

.. important::
    These math libraries are not explicit dependencies of this library.
    Install them separately before enabling.

Supported libraries:

* `GNU Multiple Precision PHP Extension <GMP_>`_
* `brick/math`_
* PEAR's `Math_BigInteger`_

.. code-block:: php

    <?php

    use SandFox\Bencode\Bencode;

    // GMP
    $data = Bencode::decode(
        "d3:inti79228162514264337593543950336ee",
        bigInt: Bencode\BigInt::GMP,
    );
    //  ['int' => gmp_init(
    //      '79228162514264337593543950336'
    //  )]

    // brick/math
    $data = Bencode::decode(
        "d3:inti79228162514264337593543950336ee",
        bigInt: Bencode\BigInt::BRICK_MATH,
    );
    //  ['int' => \Brick\Math\BigInteger::of(
    //      '79228162514264337593543950336'
    //  )]

    // Math_BigInteger from PEAR
    $data = Bencode::decode(
        "d3:inti79228162514264337593543950336ee",
        bigInt: Bencode\BigInt::PEAR,
    );
    //  ['int' => new \Math_BigInteger(
    //      '79228162514264337593543950336'
    //  )]

.. _GMP: https://www.php.net/manual/en/book.gmp.php
.. _brick/math: https://github.com/brick/math
.. _Math_BigInteger: https://pear.php.net/package/Math_BigInteger

Internal Type
-------------

.. versionadded:: 1.6/2.6

The library also has built in ``BigIntType``.
It does not require any external dependencies but also does not allow any manipulation.

.. code-block:: php

    <?php

    use SandFox\Bencode\Bencode;

    $data = Bencode::decode(
        "d3:inti79228162514264337593543950336ee",
        bigInt: Bencode\BigInt::INTERNAL,
    );
    //  ['int' => new \SandFox\Bencode\Types\BigIntType(
    //      '79228162514264337593543950336'
    //  )]

BigIntType is a value object with several getters:

.. code-block:: php

    <?php

    use SandFox\Bencode\Bencode;

    // simple string representation:
    $str = $data->value; // readonly property
    // converters to the supported libraries:
    $obj = $data->toGMP();
    $obj = $data->toPear();
    $obj = $data->toBrickMath();

Custom Handling
---------------

.. versionadded:: 1.6/2.6
.. deprecated:: 3.1 Passing class names as handlers will be removed in 4.0

Like listType and dictType you can use a callable:

.. code-block:: php

    <?php

    use SandFox\Bencode\Bencode;

    $data = Bencode::decode(
        "d3:inti79228162514264337593543950336ee",
        bigInt: fn (string $value) => $value,
    ); // ['int' => '79228162514264337593543950336']

Working with files
==================

Load data from a file:

.. code-block:: php

    <?php

    use SandFox\Bencode\Bencode;

    $data = Bencode::load('testfile.torrent');

Working with streams
====================

.. versionadded:: 1.5/2.5

Load data from a seekable readable stream:

.. code-block:: php

    <?php

    use SandFox\Bencode\Bencode;

    $data = Bencode::decodeStream(fopen('...', 'r'));

Options Array
=============

.. deprecated:: 3.1

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

.. versionadded:: 1.7/2.7/3.0

Decoder object can be configured on creation and used multiple times:

.. code-block:: php

    <?php

    use SandFox\Bencode\Decoder;

    $decoder = new Decoder(bigInt: Bencode\BigInt::INTERNAL);
    // all calls available:
    $decoder->decode($encoded);
    $decoder->decodeStream($encoded, $stream);
    $decoder->load($filename);
