Upgrade Notes
#############

Upgrade from 2.x to 3.x
=======================

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

Upgrade from 1.x to 2.x
=======================

Main breaking changes:

* Required PHP version was bumped to 8.0.
  Upgrade your interpreter.
* Legacy namespace ``SandFoxMe`` was removed.
  You should search and replace ``SandFoxMe\Bencode`` with ``SandFox\Bencode`` in your code if you haven't done it already.

