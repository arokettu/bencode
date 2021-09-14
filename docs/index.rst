Bencode
#######

|Packagist| |GitLab| |GitHub| |Bitbucket| |Gitea|

PHP Bencode Encoder/Decoder

Bencode_ is the encoding used by the peer-to-peer file sharing system
BitTorrent_ for storing and transmitting loosely structured data.

This is a pure PHP library that allows you to encode and decode Bencode data.

Installation
============

.. code-block:: bash

   composer require 'sandfoxme/bencode'

Encoding
========

Scalars and arrays
------------------

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
-------

.. note:: Parameter order is not guaranteed for options, use named parameters

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
-------------------

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

Decoding
========

.. note:: Parameter order is not guaranteed for options, use named parameters

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
--------------

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
    // save data to a bencoded file
    Bencode::dump('testfile.torrent', $data);

Working with streams
====================

.. code-block:: php

    <?php

    use SandFox\Bencode\Bencode;

    // load data from a bencoded seekable readable stream
    $data = Bencode::decodeStream(fopen('...', 'r'));
    // save data to a bencoded writable stream or to a new php://temp if no stream is specified
    Bencode::encodeToStream($data, fopen('...', 'w'));

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

Encoder and Decoder objects
===========================

3.0 added Encoder and Decoder objects that can be configured first.

.. code-block:: php

    <?php

    use SandFox\Bencode\Bencode;
    use SandFox\Bencode\Encoder;
    use SandFox\Bencode\Decoder;

    $encoder = new Encoder(useStringable: true);
    // all calls available:
    $encoder->encode($data);
    $encoder->encodeToStream($data, $stream);
    $encoder->dump($data, $filename);

    $decoder = new Decoder(bigInt: Bencode\BigInt::INTERNAL);
    // all calls available:
    $decoder->decode($encoded);
    $decoder->decodeStream($encoded, $stream);
    $decoder->load($filename);

Upgrade from 2.x
================

Main breaking changes:

* Required PHP version was bumped to 8.1.
  Upgrade your interpreter.
* Decoding:

  * Removed deprecated options: ``dictionaryType`` (use ``dictType``), ``useGMP`` (use ``bigInt: Bencode\BigInt::GMP``)
  * ``Bencode\BigInt`` and ``Bencode\Collection`` are now enums,
    therefore ``dictType``, ``listType``, ``bigInt`` params no longer accept bare string values
    (like ``'array'`` or ``'object'`` or ``'gmp'``).
    If you already use constants nothing will change for you.

* Encoding:

  * Traversables no longer become dictionaries by default.
    You need to wrap them with ``DictType``.
  * Stringables no longer become strings by default.
    Use ``useStringable: true`` to return old behavior.
  * ``dump($filename, $data)`` became ``dump($data, $filename)`` for consistency with streams.

    .. code-block:: php

        <?php

        // code that works in all versions:
        if (class_exists(\SandFox\Bencode\Encoder::class)) {
            // bencode v3
            $success = (new \SandFox\Bencode\Encoder([...$optionsHere]))->dump($data, $filename);
            // or
            $success = \SandFox\Bencode\Bencode::dump($data, $filename, [...$optionsHere]);
        } else {
            // bencode v1/v2
            $success = \SandFox\Bencode\Bencode::dump($filename, $data, [...$optionsHere]);
        }

        // Or use named parameters in PHP 8.0+:
        $success = \SandFox\Bencode\Bencode::dump(
            data: $data,
            filename: $filename,
            [...$optionsHere]
        );

  * ``bencodeSerialize`` now declares ``mixed`` return type.

License
=======

The library is available as open source under the terms of the `MIT License`_.

.. _Bencode:            https://en.wikipedia.org/wiki/Bencode
.. _BitTorrent:         https://en.wikipedia.org/wiki/BitTorrent
.. _JsonSerializable:   http://php.net/manual/en/class.jsonserializable.php
.. _MIT License:        https://opensource.org/licenses/MIT

.. |Packagist|  image:: https://img.shields.io/packagist/v/sandfoxme/bencode.svg?style=flat-square
   :target:     https://packagist.org/packages/sandfoxme/bencode
.. |GitHub|     image:: https://img.shields.io/badge/get%20on-GitHub-informational.svg?style=flat-square&logo=github
   :target:     https://github.com/arokettu/bencode
.. |GitLab|     image:: https://img.shields.io/badge/get%20on-GitLab-informational.svg?style=flat-square&logo=gitlab
   :target:     https://gitlab.com/sandfox/bencode
.. |Bitbucket|  image:: https://img.shields.io/badge/get%20on-Bitbucket-informational.svg?style=flat-square&logo=bitbucket
   :target:     https://bitbucket.org/sandfox/bencode
.. |Gitea|      image:: https://img.shields.io/badge/get%20on-Gitea-informational.svg?style=flat-square&logo=gitea
   :target:     https://sandfox.org/sandfox/bencode
