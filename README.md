# PHP Bencode Encoder/Decoder

[![Packagist](https://img.shields.io/packagist/v/sandfoxme/bencode.svg)](https://packagist.org/packages/sandfoxme/bencode)
[![PHP](https://img.shields.io/packagist/php-v/sandfoxme/bencode/1.x-dev.svg?label=php%20for%201.x)](https://packagist.org/packages/sandfoxme/bencode)
[![PHP](https://img.shields.io/packagist/php-v/sandfoxme/bencode/2.x-dev.svg?label=php%20for%202.x)](https://packagist.org/packages/sandfoxme/bencode)
[![License](https://img.shields.io/github/license/sandfoxme/bencode.svg)](https://opensource.org/licenses/MIT)
[![Travis](https://img.shields.io/travis/arokettu/bencode.svg)](https://travis-ci.org/github/arokettu/bencode)

[Bencode] is the encoding used by the peer-to-peer file sharing system
[BitTorrent] for storing and transmitting loosely structured data.

This is a pure PHP library that allows you to encode and decode Bencode data.

## Installation

```bash
composer require 'sandfoxme/bencode'
```

## Simple use

```php
<?php

\SandFox\Bencode\Bencode::encode(['info' => ['length' => 12345, 'name' => 'Bencoded demo']]);
\SandFox\Bencode\Bencode::decode('d4:infod6:lengthi12345e4:name13:Bencoded demoee');
```

## Documentation

Read full documentation here: <https://sandfox.dev/php/bencode.html>

Documentation for earlier versions can be found on Read the Docs:
<https://bencode.readthedocs.io/en/1.x/>

## Support

Please file issues on our main repo at GitLab: <https://gitlab.com/sandfox/bencode/-/issues>

## License

The library is available as open source under the terms of the [MIT License].

[Bencode]:      https://en.wikipedia.org/wiki/Bencode
[BitTorrent]:   https://en.wikipedia.org/wiki/BitTorrent
[MIT License]:  https://opensource.org/licenses/MIT
