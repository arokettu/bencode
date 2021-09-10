<?php

declare(strict_types=1);

namespace SandFox\Bencode\Tests;

use PHPUnit\Framework\TestCase;
use SandFox\Bencode\Bencode;
use SandFox\Bencode\Exceptions\InvalidArgumentException;

class EncodeTest extends TestCase
{
    public function testUnknownType(): void
    {
        // We can't serialize resources
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Bencode doesn't know how to serialize an instance of resource");

        Bencode::encode(fopen(__FILE__, 'r'));
    }

    public function testUnknownObject(): void
    {
        // We can't serialize non-stringable objects
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Bencode doesn't know how to serialize an instance of " . get_class($this));

        Bencode::encode($this);
    }

    public function testNoRepeatedKeys()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Dictionary contains repeated keys: 'key'");

        Bencode::encode(
            (function () {
                yield 'key' => 'value1';
                yield 'key' => 'value2';
            })()
        );
    }
}
