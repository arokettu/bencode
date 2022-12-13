<?php

declare(strict_types=1);

namespace Arokettu\Bencode\Tests;

use Arokettu\Bencode\Bencode;
use Arokettu\Bencode\Exceptions\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

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
        $this->expectExceptionMessage("Bencode doesn't know how to serialize an instance of " . self::class);

        Bencode::encode($this);
    }
}
