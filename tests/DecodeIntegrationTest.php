<?php

declare(strict_types=1);

namespace Arokettu\Bencode\Tests;

use Arokettu\Bencode\Bencode;
use ArrayObject;
use PHPUnit\Framework\TestCase;
use stdClass;

class DecodeIntegrationTest extends TestCase
{
    public function testAllFeatures(): void
    {
        $value = new ArrayObject([
            123,
            'String',
            new ArrayObject([]),
            new ArrayObject([new stdClass()]),
            (object)[
                'i' => 213,
                's' => 'string',
                'l' => new ArrayObject([]),
                'd' => (object)[
                    'test' => 'test',
                ],
            ],
            456,
        ]);

        $bencode = 'l' .
            'i123e' .
            '6:String' .
            'le' .
            'l' .
                'de' .
            'e' .
            'd' .
                '1:d' . 'd' .
                    '4:test' . '4:test' .
                'e' .
                '1:i' . 'i213e' .
                '1:l' . 'le' .
                '1:s' . '6:string' .
            'e' .
            'i456e' .
        'e';

        $decoded = Bencode::decode(
            $bencode,
            listType: fn ($list) => new ArrayObject(iterator_to_array($list)),
            dictType: Bencode\Collection::OBJECT,
        );

        self::assertEquals($value, $decoded);
        self::assertEquals(ArrayObject::class, $decoded[3]::class);
        self::assertEquals(stdClass::class, $decoded[4]::class);
    }
}
