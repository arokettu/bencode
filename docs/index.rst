Bencode
#######

|Packagist| |Gitlab| |GitHub| |Bitbucket| |Gitea|

PHP Bencode Encoder/Decoder

Bencode_ is the encoding used by the peer-to-peer file sharing system
BitTorrent_ for storing and transmitting loosely structured data.

This is a pure PHP library that allows you to encode and decode Bencode data.

Installation
============

Run ``composer require 'sandfoxme/bencode:^1.3'``.

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
       'bool'      => true,            // bool will be an integer 1 or 0
       'string'    => "test\0test",    // string can contain any binary data
   ]); // "d3:arrli1ei2ei3ei4ee4:booli1e5:float6:3.14153:inti123e6:string9:test\0teste"

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
       public function bencodeSerialize() {
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
   $data = Bencode::decode("...", [
       'dictionaryType'    => ArrayObject::class, // pass class name, new $type($array) will be created
       'listType'          => function ($array) { // or callback for greater flexibility
           return new ArrayObject($array, ArrayObject::ARRAY_AS_PROPS);
       },
   ]);
   // default value for both types is 'array'. you can also use 'object' for stdClass

Working with files
==================

.. code-block:: php

   <?php

   use SandFox\Bencode\Bencode;

   $data = Bencode::load('testfile.torrent'); // load data from bencoded file
   Bencode::dump('testfile.torrent', $data); // save data to the bencoded file

License
=======

The library is available as open source under the terms of the `MIT License`_.

.. _Bencode:            https://en.wikipedia.org/wiki/Bencode
.. _BitTorrent:         https://en.wikipedia.org/wiki/BitTorrent
.. _JsonSerializable:   http://php.net/manual/en/class.jsonserializable.php
.. _MIT License:        https://opensource.org/licenses/MIT

.. |Packagist|  image:: https://img.shields.io/packagist/v/sandfoxme/bencode.svg
   :target: https://packagist.org/packages/sandfoxme/bencode
.. |GitHub|     image:: https://img.shields.io/badge/get%20on-GitHub-informational.svg?logo=github
   :target:     https://github.com/arokettu/bencode
.. |GitLab|     image:: https://img.shields.io/badge/get%20on-Gitlab-informational.svg?logo=gitlab
   :target:     https://gitlab.com/sandfox/bencode
.. |Bitbucket|  image:: https://img.shields.io/badge/get%20on-Bitbucket-informational.svg?logo=bitbucket
   :target:     https://bitbucket.org/sandfox/bencode
.. |Gitea|      image:: https://img.shields.io/badge/get%20on-Gitea-informational.svg
   :target:     https://git.sandfox.dev/sandfox/bencode
