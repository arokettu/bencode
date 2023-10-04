# PHP Bencode Encoder/Decoder

[![Packagist](https://img.shields.io/packagist/v/arokettu/bencode.svg?style=flat-square)](https://packagist.org/packages/arokettu/bencode)
[![PHP](https://img.shields.io/packagist/php-v/arokettu/bencode.svg?style=flat-square)](https://packagist.org/packages/arokettu/bencode)
[![Packagist](https://img.shields.io/github/license/arokettu/bencode.svg?style=flat-square)](https://opensource.org/licenses/MIT)
[![Gitlab pipeline status](https://img.shields.io/gitlab/pipeline/sandfox/bencode/master.svg?style=flat-square)](https://gitlab.com/sandfox/bencode/-/pipelines)
[![Codecov](https://img.shields.io/codecov/c/gl/sandfox/bencode?style=flat-square)](https://codecov.io/gl/sandfox/bencode/)

[Bencode] is the encoding used by the peer-to-peer file sharing system
[BitTorrent] for storing and transmitting loosely structured data.

This is a pure PHP library that allows you to encode and decode Bencode data.

## Installation

```bash
composer require 'arokettu/bencode'
```

Supported versions:

* 1.x (bugfixes only, PHP 7.0+)
* 2.x (bugfixes only, PHP 8.0+)
* 3.x (bugfixes only, PHP 8.1+)
* 4.x (current, PHP 8.1+)

## Simple use

```php
<?php

\Arokettu\Bencode\Bencode::encode(['info' => ['length' => 12345, 'name' => 'Bencoded demo']]);
\Arokettu\Bencode\Bencode::decode('d4:infod6:lengthi12345e4:name13:Bencoded demoee');
```

## Documentation

Read full documentation here: <https://sandfox.dev/php/bencode.html>

Documentation for all supported versions can be found on Read the Docs:

* 1.x: <https://bencode.readthedocs.io/en/1.x/>
* 2.x: <https://bencode.readthedocs.io/en/2.x/>
* 3.x: <https://bencode.readthedocs.io/en/3.x/>
* 4.x: <https://bencode.readthedocs.io/>

## Support

Please file issues on our main repo at GitLab: <https://gitlab.com/sandfox/bencode/-/issues>

Feel free to ask any questions in our room on Gitter: <https://gitter.im/arokettu/community>

## License

The library is available as open source under the terms of the [MIT License].

[Bencode]:      https://en.wikipedia.org/wiki/Bencode
[BitTorrent]:   https://en.wikipedia.org/wiki/BitTorrent
[MIT License]:  https://opensource.org/licenses/MIT
