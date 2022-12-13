Upgrade Notes
#############

Upgrade from 3.x to 4.x
=======================

* The package was renamed from ``sandfoxme/bencode`` to ``arokettu/bencode``
* The package namespace was changed from ``SandFox\Bencode`` to ``Arokettu\Bencode``

  * A custom autoloader to alias ``SandFox\Bencode`` to ``Arokettu\Bencode`` was added to 1.8.0, 2.8.0, and 3.1.0
* Dictionaries are now converted to the ArrayObject by default
* $options arrays were removed
* Closures passed to ``listType``, ``dictType``, and ``bigInt`` must handle iterables instead of arrays now
* Class names can no longer be passed to ``listType``, ``dictType``, and ``bigInt``

  .. code-block:: php

        <?php

        use Arokettu\Bencode\Bencode;

        // 3.x
        $data = Bencode::decode(
            "...",
            listType: CustomHandler::class,
        );
        // should become in 4.0
        $data = Bencode::decode(
            "...",
            listType: fn (iterable $list) => new CustomHandler([...$list]),
        );

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
    If you use constants, nothing will change for you.

* Encoding:

  * Traversables no longer become dictionaries by default.
    You need to wrap them with ``DictType``.
  * Stringables no longer become strings by default.
    Use ``useStringable: true`` to return old behavior.
  * ``Bencode::dump($filename, $data)`` became ``Bencode::dump($data, $filename)`` for consistency with streams.

    .. code-block:: php

        <?php

        // Use Encoder object in 1.7+/2.7+/3.0+:
        $success = (new \SandFox\Bencode\Encoder([...$optionsHere]))->dump($data, $filename);

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
