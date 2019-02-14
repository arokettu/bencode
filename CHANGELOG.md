# 1.3.0

*Feb 14, 2019*

* Increased parser speed and reduced memory consumption
* Base namespace is now `SandFox\Bencode`. Compatibility is kept for now
* Fixed tests for PHP 8

# 1.2.0

*Feb 14, 2018*

* Added `BencodeSerializable` interface

# 1.1.2

*Dec 12, 2017*

* Throw a Runtime Exception when trying to use the library with Mbstring Function Overloading on

# 1.1.1

*Mar 30, 2017*

* ListType can now wrap arrays

# 1.1.0

*Mar 29, 2017*

* boolean is now converted to integer
* `Bencode::dump` now returns success as boolean
* Fixed: decoded junk at the end of the string replaced entire parsed data if it also was valid bencode
* PHP 7.0 is now required instead of PHP 7.1
* Tests!

# 1.0.1

*Mar 22, 2017*

* Add stdClass as list/dict decoding option

# 1.0.0

*Mar 22, 2017*

Initial release
