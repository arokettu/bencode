<?php

declare(strict_types=1);

namespace SandFox\Bencode\Tests;

use PHPUnit\Framework\TestCase;
use SandFox\Bencode\Bencode;
use SandFox\Bencode\Exceptions\InvalidArgumentException;

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
        $this->expectException(InvalidArgumentException::class);
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
