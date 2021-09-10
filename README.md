# PHP Bencode Encoder/Decoder

[![Packagist](https://img.shields.io/packagist/v/sandfoxme/bencode.svg?style=flat-square)](https://packagist.org/packages/sandfoxme/bencode)
[![PHP](https://img.shields.io/packagist/php-v/sandfoxme/bencode/1.x-dev.svg?style=flat-square&label=php%20for%201.x)](https://packagist.org/packages/sandfoxme/bencode)
[![PHP](https://img.shields.io/packagist/php-v/sandfoxme/bencode/2.x-dev.svg?style=flat-square&label=php%20for%202.x)](https://packagist.org/packages/sandfoxme/bencode)
[![PHP](https://img.shields.io/packagist/php-v/sandfoxme/bencode/3.x-dev.svg?style=flat-square&label=php%20for%203.x)](https://packagist.org/packages/sandfoxme/bencode)
[![Packagist](https://img.shields.io/github/license/sandfoxme/bencode.svg?style=flat-square)](https://opensource.org/licenses/MIT)
[![Gitlab pipeline status](https://img.shields.io/gitlab/pipeline/sandfox/bencode/3.x.svg?style=flat-square)](https://gitlab.com/sandfox/bencode/-/pipelines)
[![Codecov](https://img.shields.io/codecov/c/gl/sandfox/bencode?style=flat-square)](https://codecov.io/gl/sandfox/bencode/)

[Bencode] is the encoding used by the peer-to-peer file sharing system
[BitTorrent] for storing and transmitting loosely structured data.

This is a pure PHP library that allows you to encode and decode Bencode data.

## Installation

```bash
composer require 'sandfoxme/bencode'
```

* Version 1.x supports PHP 7.0 and later
* Version 2.x supports PHP 8.0 and later and has slightly better error handling

## Simple use

```php
<?php

\SandFox\Bencode\Bencode::encode(['info' => ['length' => 12345, 'name' => 'Bencoded demo']]);
\SandFox\Bencode\Bencode::decode('d4:infod6:lengthi12345e4:name13:Bencoded demoee');
```

## Documentation

Read full documentation here: <https://sandfox.dev/php/bencode.html>

Documentation for earlier versions can be found on Read the Docs:

* 1.x: <https://bencode.readthedocs.io/en/1.x/>
* 2.x: <https://bencode.readthedocs.io/en/2.x/>

## Support

All major versions are in active support: 1.x for PHP 7.0+, 2.x for PHP 8.0+, 3.x for PHP 8.1+.

Please file issues on our main repo at GitLab: <https://gitlab.com/sandfox/bencode/-/issues>

## License

The library is available as open source under the terms of the [MIT License].

[Bencode]:      https://en.wikipedia.org/wiki/Bencode
[BitTorrent]:   https://en.wikipedia.org/wiki/BitTorrent
[MIT License]:  https://opensource.org/licenses/MIT
