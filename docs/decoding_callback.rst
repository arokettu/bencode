Decoding with Callbacks
#######################

.. highlight:: php
.. versionadded:: 4.2

Callback decoding may be useful if you don't need a complete decoding result.
Examples:

* Bencode validation
* Extraction of specific values

Callback Decoder Object
=======================

Decoder object can be configured on creation and used multiple times::

    <?php

    use Arokettu\Bencode\CallbackDecoder;

    $decoder = new CallbackDecoder(bigInt: Bencode\BigInt::INTERNAL);
    $callback = function (array $keys, mixed $value) {
        // ...
    }
    // all calls available:
    $decoder->decode($encoded, $callback);
    $decoder->decodeStream($stream, $callback);
    $decoder->load($filename, $callback);

Callback
========

Callback can be any callable with signature ``(array $keys, mixed $value): ?bool``.
For a callable object this signature can be enforced by the interface ``Arokettu\Bencode\Types\CallbackHandler``.
The callback is called for every encountered scalar.
Empty lists and dictionaries will not trigger the callback.
If the callback returns false, the parser quits.

Arguments
=========

* ``$keys``.
  An array of keys of lists and dictionaries.
  Int keys refer to lists.
  String keys refer to dictionaries.
* ``$value``.
  A scalar value nested by ``$keys``.

Example
=======

Count files for v1 torrent::

    <?php

    use Arokettu\Bencode\CallbackDecoder;

    $file = 'torrent_file.torrent';
    $decoder = new CallbackDecoder();

    $count = 0;
    $decoder->load($file, function (array $keys) use (&$count) {
        if ($keys[0] === 'info' && $keys[1] === 'files' && $keys[3] === 'path') {
            $count += 1;
        }
    });

    echo $count, PHP_EOL;
