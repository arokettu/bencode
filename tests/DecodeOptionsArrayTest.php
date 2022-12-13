<?php

declare(strict_types=1);

namespace SandFox\Bencode\Tests;

use ArrayObject;
use PHPUnit\Framework\TestCase;
use SandFox\Bencode\Bencode;
use stdClass;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;

class DecodeOptionsArrayTest extends TestCase
{
    use ExpectDeprecationTrait;

    /**
     * @group legacy
     */
    public function testOptionsArray(): void
    {
        $this->expectDeprecation(
            'Since arokettu/bencode 3.1.0: $options is deprecated, use named parameters'
        );

        $encoded = 'd4:dictd2:k1i2e2:k22:s12:k3i3e2:k42:s22:k5i5ee4:listli2e2:s1i3e2:s2i5eee';

        $decoded = Bencode::decode($encoded, [
            'listType' => fn (array $list) => new ArrayObject($list),
            'dictType' => Bencode\Collection::OBJECT,
        ]);

        $this->assertInstanceOf(ArrayObject::class, $decoded->list);
        $this->assertInstanceOf(stdClass::class, $decoded->dict);
    }
}
