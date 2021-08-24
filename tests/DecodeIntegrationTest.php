<?php

declare(strict_types=1);

namespace SandFox\Bencode\Tests;

use ArrayObject;
use PHPUnit\Framework\TestCase;
use SandFoxMe\Bencode\Bencode;
use stdClass;

class DecodeIntegrationTest extends TestCase
{
    public function testAllFeatures()
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

        $decoded = Bencode::decode($bencode, [
            'listType' => ArrayObject::class,
            'dictionaryType' => 'object',
        ]);

        self::assertEquals($value, $decoded);
        self::assertEquals(ArrayObject::class, \get_class($decoded[3]));
        self::assertEquals(stdClass::class,    \get_class($decoded[4]));
    }
}
