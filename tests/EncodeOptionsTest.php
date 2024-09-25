<?php

declare(strict_types=1);

namespace Arokettu\Bencode\Tests;

use Arokettu\Bencode\Bencode;
use Arokettu\Bencode\Exceptions\ValueNotSerializableException;
use PHPUnit\Framework\TestCase;

class EncodeOptionsTest extends TestCase
{
    public function testStringable(): void
    {
        // object with __toString
        self::assertEquals('6:string', Bencode::encode(new class {
            public function __toString(): string
            {
                return 'string';
            }
        }, useStringable: true));
    }

    public function testNoStringable(): void
    {
        $this->expectException(ValueNotSerializableException::class);
        $this->expectExceptionMessage("Bencode doesn't know how to serialize an instance of class@anonymous");

        // object with __toString
        Bencode::encode(new class {
            public function __toString(): string
            {
                return 'string';
            }
        });
    }
}
