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

.. warning:: *Possibly breaking change in 1.4 and 2.4:*

    Before 1.4 and 2.4 ``null`` was encoded as empty string and ``false`` was encoded as 0.
    Since bencode spec doesn't have bool and null values, it is not considered a bc break.
    Judging by info[private] behavior in BitTorrent spec, the old behavior could be considered as a bug.

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

.. code-block:: php

    <?php

    use SandFox\Bencode\Bencode;

    // traversable objects and stdClass become dictionaries
    $encoded = Bencode::encode(new ArrayObject([1,2,3])); // "d1:0i1e1:1i2e1:2i3ee"
    $std = new stdClass();
    $std->a = '123';
    $std->b = 456;
    $encoded = Bencode::encode($std); // "d1:a3:1231:bi456ee"

    // you can force traversable to become a list by wrapping it with ListType
    // keys will be discarded in that case
    use SandFox\Bencode\Types\ListType;
    $encoded = Bencode::encode(new ListType(new ArrayObject([1,2,3]))); // "li1ei2ei3ee"

    // other objects will be converted to string if possible or generate an error if not
    class ToString
    {
        public function __toString()
        {
            return 'I am string';
        }
    }

    $encoded = Bencode::encode(new ToString()); // "11:I am string"

    // Since 1.5 and 2.5: GMP object becomes integer
    // Since 1.6 and 2.6 Pear's Math_BigInteger and brick/math
    //      and internal type BigIntType (simple numeric string wrapper) are also supported
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

    $encoded = Bencode::encode($file); // "d5:class6:MyFile4:name14:myfile.torrent4:sizei5242880ee"

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
        dictType: ArrayObject::class, // pass class name, new $type($array) will be created
        listType: function ($array) { // or callback for greater flexibility
            return new ArrayObject($array, ArrayObject::ARRAY_AS_PROPS);
        },
    ]);
    // default value for both types is 'array'. you can also use 'object' for stdClass

Large integers
--------------

.. important::
    These math libraries are not explicit dependencies of this library.
    Install them separately before enabling.

By default the library only works with a native integer type but if you need to use large integers,
for example, if you try to parse a torrent file for a >= 4GB file on a 32 bit system,
you can enable big integer support.

Versions 1.5 and 2.5 added support for GMP_:

.. code-block:: php

    <?php

    use SandFox\Bencode\Bencode;

    // Enable useGMP option to decode huge integers to the GMP object
    $data = Bencode::decode(
        "d3:gmpi79228162514264337593543950336ee",
        useGMP: true,
    ]; // ['gmp' => gmp_init('79228162514264337593543950336')]

Versions 1.6 and 2.6 added support for `brick/math`_ and Math_BigInteger_, and custom handlers:

.. code-block:: php

    <?php

    use SandFox\Bencode\Bencode;

    // GMP
    $data = Bencode::decode(
        "d3:inti79228162514264337593543950336ee",
        bigInt: Bencode\BigInt::GMP, // same as ['useGMP' => true]
    ]; // ['int' => gmp_init('79228162514264337593543950336')]

    // brick/math
    $data = Bencode::decode(
        "d3:inti79228162514264337593543950336ee",
        bigInt: Bencode\BigInt::BRICK_MATH,
    ]; // ['int' => \Brick\Math\BigInteger::of('79228162514264337593543950336')]

    // Math_BigInteger from PEAR
    $data = Bencode::decode(
        "d3:inti79228162514264337593543950336ee",
        bigInt: Bencode\BigInt::PEAR,
    ]; // ['int' => new \Math_BigInteger('79228162514264337593543950336')]

    // Internal BigIntType class
    // does not require any external dependencies but also does not allow any manipulation
    $data = Bencode::decode(
        "d3:inti79228162514264337593543950336ee",
        bigInt: Bencode\BigInt::INTERNAL
    ]; // ['int' => new \SandFox\Bencode\Types\BigIntType('79228162514264337593543950336')]
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
        bigInt: fn($v) => v,
    ]; // ['int' => '79228162514264337593543950336']
    $data = Bencode::decode(
        "d3:inti79228162514264337593543950336ee",
        bigInt: MyBigIntHandler::class,
    ]; // ['int' => new MyBigIntHandler('79228162514264337593543950336')]]

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

Upgrade from 1.x
================

Main breaking changes:

* Required PHP version was bumped to 8.0.
  Upgrade your interpreter.
* Legacy namespace ``SandFoxMe`` was removed.
  You should search and replace ``SandFoxMe\Bencode`` with ``SandFox\Bencode`` in your code if you haven't done it already.

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
