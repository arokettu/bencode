# Changelog

## 2.x

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

* PHP 8 is required
* Legacy namespace `SandFoxMe\Bencode` is removed
* Encode now throws an error if it encounters a value that cannot be serialized

## 1.x

### 1.4.0

*Nov 10, 2020*

* Make spec compliant BitTorrent code simpler: `null` and `false` values are now skipped on encoding
* Add `'dictType'` alias for `'dictionaryType'` for 2.3 compatibility

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

* Add stdClass as list/dict decoding option

### 1.0.0

*Mar 22, 2017*

Initial release
