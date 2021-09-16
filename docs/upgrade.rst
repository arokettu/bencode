Upgrade Notes
#############

Upgrade from 1.x to 2.x
=======================

Main breaking changes:

* Required PHP version was bumped to 8.0.
  Upgrade your interpreter.
* Legacy namespace ``SandFoxMe`` was removed.
  You should search and replace ``SandFoxMe\Bencode`` with ``SandFox\Bencode`` in your code if you haven't done it already.
