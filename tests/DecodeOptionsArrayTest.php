<?php

declare(strict_types=1);

namespace SandFox\Bencode\Tests;

use ArrayObject;
use PHPUnit\Framework\TestCase;
use SandFox\Bencode\Bencode;
use stdClass;

class DecodeOptionsArrayTest extends TestCase
{
    public function testOptionsArray(): void
    {
        $encoded = 'd4:dictd2:k1i2e2:k22:s12:k3i3e2:k42:s22:k5i5ee4:listli2e2:s1i3e2:s2i5eee';

        $decoded = Bencode::decode($encoded, [
            'listType' => fn (array $list) => new ArrayObject($list),
            'dictType' => Bencode\Collection::OBJECT,
        ]);

        $this->assertInstanceOf(ArrayObject::class, $decoded->list);
        $this->assertInstanceOf(stdClass::class, $decoded->dict);
    }
}
