<?php

declare(strict_types=1);

namespace SandFox\Bencode\Tests;

use ArrayObject;
use PHPUnit\Framework\TestCase;
use SandFox\Bencode\Bencode;
use stdClass;

class DecodeLegacyOptionsTest extends TestCase
{
    public function testLegacyOptionsArray()
    {
        $encoded = 'd4:dictd2:k1i2e2:k22:s12:k3i3e2:k42:s22:k5i5ee4:listli2e2:s1i3e2:s2i5eee';

        $decoded = Bencode::decode($encoded, [
            'listType' => ArrayObject::class,
            'dictionaryType' => 'object',
        ]);

        $this->assertInstanceOf(ArrayObject::class, $decoded->list);
        $this->assertInstanceOf(stdClass::class, $decoded->dict);
    }

    public function testLegacyDictOption()
    {
        $dict = [
            'k1' => 2,
            'k2' => 's1',
            'k3' => 3,
            'k4' => 's2',
            'k5' => 5,
        ];
        $encoded = 'd2:k1i2e2:k22:s12:k3i3e2:k42:s22:k5i5ee';

        $object = (object)$dict;
        $decodedObject = Bencode::decode($encoded, dictionaryType: 'object');

        $this->assertEquals(stdClass::class, get_class($decodedObject));
        $this->assertEquals($object, $decodedObject);
    }
}
