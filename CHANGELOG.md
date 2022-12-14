# Changelog

## 3.x

### 3.1.1

*Dec 14, 2022*

* `sandfoxme/bencode` is now provided by the package

### 3.1.0

*Dec 13, 2022*

* $options arrays are deprecated
* Passing class names to `listType`, `dictType`, and `bigInt` is deprecated
* Aliased all classes in `SandFox\Bencode\*` to `Arokettu\Bencode\*` in preparation for 4.0

### 3.0.3

*Oct 24, 2021*

* dump() now throws exception if the file is not writable
* load() now throws exception if the file is not readable

### 3.0.2

*Oct 23, 2021*

* Objects serialized to empty values are now allowed on non-root levels

### 3.0.1

*Sep 25, 2021*

* Future compatible stream check

### 3.0.0

*Sep 17, 2021*

3.0.0 was branched from 2.6.1

* PHP 8.1 is required
* Decoding:
    * Removed deprecated options: ``dictionaryType`` (use ``dictType``), ``useGMP`` (use ``bigInt: Bencode\BigInt::GMP``)
    * ``Bencode\BigInt`` and ``Bencode\Collection`` are now enums,
      therefore ``dictType``, ``listType``, ``bigInt`` params no longer accept bare string values
      (like ``'array'`` or ``'object'`` or ``'gmp'``).
* Encoding:
    * Traversables no longer become dictionaries by default.
      You need to wrap them with ``DictType``.
    * Stringables no longer become strings by default.
      Use ``useStringable: true`` to return old behavior.
    * ``dump($filename, $data)`` became ``dump($data, $filename)`` for consistency with streams.
* `Decoder` and `Encoder` objects that can be pre-configured and then used with consistent options.
* `bencodeSerialize` now declares `mixed` return type

## 2.x

### 2.8.1

*Dec 14, 2022*

* `sandfoxme/bencode` is now provided by the package

### 2.8.0

*Dec 13, 2022*

* Alias all classes in `SandFox\Bencode\*` to `Arokettu\Bencode\*` in preparation for 4.0

### 2.7.4

*Nov 30, 2021*

* symfony/contracts v3 is now allowed

### 2.7.3

*Oct 24, 2021*

* dump() now throws exception if the file is not writable
* load() now throws exception if the file is not readable

### 2.7.2

*Oct 23, 2021*

* Objects serialized to empty values are now allowed on non-root levels

### 2.7.1

*Sep 25, 2021*

* Future compatible stream check

### 2.7.0

*Sep 17, 2021*

* Decoder and Encoder are backported from 3.x
* `DictType` backported from 3.x
* `useJsonSerializable` backported from 3.x
* `useGMP` is marked as deprecated
* Fixed `'useGMP'` in options array causing crash

### 2.6.1

*Sep 10, 2021*

* Fixed possible invalid dictionary encoding when traversable returns non unique keys

### 2.6.0

*Feb 14, 2021*

* Expanded big integer support:
  * `brick/math`
  * `Math_BigInteger`
  * Custom BigIntType numeric string wrapper
  * Callback and custom class name

### 2.5.0

*Feb 3, 2021*

* Added stream API
* Added GMP support

### 2.4.0

*Nov 10, 2020*

* Make spec compliant BitTorrent code simpler: `null` and `false` values are now skipped on encoding
* Remove deprecation warning for options array

### 2.3.0

*Oct 4, 2020*

* Shorten `dictionaryType` to `dictType`. `dictionaryType` will be removed in 3.0
* Trigger silent deprecations for deprecated stuff

### 2.2.0

*Oct 3, 2020*

* Update `dump()` and `load()` signatures to match `encode()` and `decode()` 

### 2.1.0

*Aug 5, 2020*

* Replace Becnode::decode() options array with named parameters.
  Options array is now deprecated and will be removed in 3.0
* Engine optimizations

### 2.0.0

*Jun 30, 2020*

2.0.0 was branched from 1.3.0

* PHP 8 is required
* Legacy namespace `SandFoxMe\Bencode` is removed
* Encode now throws an error if it encounters a value that cannot be serialized

## 1.x

### 1.8.1

*Dec 14, 2022*

* `sandfoxme/bencode` is now provided by the package

### 1.8.0

*Dec 13, 2022*

* Alias all classes in `SandFox\Bencode\*` to `Arokettu\Bencode\*` in preparation for 4.0 

### 1.7.3

*Oct 24, 2021*

* dump() now throws exception if the file is not writable
* load() now throws exception if the file is not readable

### 1.7.2

*Oct 23, 2021*

* Objects serialized to empty values are now allowed on non-root levels 

### 1.7.1

*Sep 25, 2021*

* Future compatible stream check

### 1.7.0

*Sep 17, 2021*

* Decoder and Encoder are backported from 3.x
* `DictType` backported from 3.x
* `useJsonSerializable` backported from 3.x

### 1.6.2

*Sep 10, 2021*

* Fixed possible invalid dictionary encoding when traversable returns non unique keys

### 1.6.1

*Feb 14, 2021*

* Fixed invalid `BigIntType::assertValidInteger` visibility
* Added missing `@internal` and strict type markings

### 1.6.0

*Feb 14, 2021*

* Expanded big integer support:
    * `brick/math`
    * `Math_BigInteger`
    * Custom BigIntType numeric string wrapper
    * Callback and custom class name

### 1.5.0

*Feb 3, 2021*

* Added stream API
* Added GMP support

### 1.4.0

*Nov 10, 2020*

* Made spec compliant BitTorrent code simpler: `null` and `false` values are now skipped on encoding
* Added `'dictType'` alias for `'dictionaryType'` for 2.3 compatibility

### 1.3.0

*Feb 14, 2019*

* Increased parser speed and reduced memory consumption
* Base namespace is now `SandFox\Bencode`. Compatibility is kept for now
* Fixed tests for PHP 8

### 1.2.0

*Feb 14, 2018*

* Added `BencodeSerializable` interface

### 1.1.2

*Dec 12, 2017*

* Throw a Runtime Exception when trying to use the library with Mbstring Function Overloading on

### 1.1.1

*Mar 30, 2017*

* ListType can now wrap arrays

### 1.1.0

*Mar 29, 2017*

* boolean is now converted to integer
* `Bencode::dump` now returns success as boolean
* Fixed: decoded junk at the end of the string replaced entire parsed data if it also was valid bencode
* PHP 7.0 is now required instead of PHP 7.1
* Tests!

### 1.0.1

*Mar 22, 2017*

* Added stdClass as list/dict decoding option

### 1.0.0

*Mar 22, 2017*

Initial release
