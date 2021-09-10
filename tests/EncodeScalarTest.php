<?php

declare(strict_types=1);

namespace SandFox\Bencode\Tests;

use PHPUnit\Framework\TestCase;
use SandFox\Bencode\Bencode;

class EncodeScalarTest extends TestCase
{
    public function testInteger(): void
    {
        // positive
        self::assertEquals('i314e', Bencode::encode(314));

        // negative
        self::assertEquals('i-512e', Bencode::encode(-512));

        // zero
        self::assertEquals('i0e', Bencode::encode(0));

        // scalars converted to integer
        self::assertEquals('i1e', Bencode::encode(true));
    }

    public function testString(): void
    {
        // arbitrary
        self::assertEquals('11:test string', Bencode::encode('test string'));

        // special characters
        self::assertEquals("25:zero\0newline\nsymblol05\x05ok", Bencode::encode("zero\0newline\nsymblol05\x05ok"));

        // empty
        self::assertEquals('0:', Bencode::encode(''));

        // unicode. prefix number reflects the number if bytes
        self::assertEquals('9:日本語', Bencode::encode('日本語'));

        // scalars converted to string
        self::assertEquals('6:3.1416', Bencode::encode(3.1416));
    }
}
