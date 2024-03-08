<?php

declare(strict_types=1);

namespace Arokettu\Bencode\Tests;

use Arokettu\Bencode\Bencode;
use Arokettu\Bencode\CallbackDecoder;
use Arokettu\Bencode\Tests\Helpers\CallbackCombiner;
use ArrayObject;
use PHPUnit\Framework\TestCase;

class CallbackDecodeIntegrationTest extends TestCase
{
    public function testAllFeatures(): void
    {
        $value = new ArrayObject([
            123,
            'String',
            // new ArrayObject([]), // empty will be ignored
            // new ArrayObject([new stdClass()]), // empty will be ignored
            [
                'i' => 213,
                's' => 'string',
                // 'l' => new ArrayObject([]), // empty will be ignored
                'd' => [
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

        $decoded = CallbackCombiner::parse(new CallbackDecoder(), $bencode);

        self::assertEquals($value, $decoded);
    }

    // various nestings

    public function testDictListDict(): void
    {
        $value = [
            'a' => 'x',
            'b' => 'y',
            'c' => [
                1,
                2,
                3 => [
                    'a' => 'x',
                    'b' => 'y',
                    'c' => 'z',
                ],
                4,
            ],
            'd' => 'z',
        ];

        $encoded = Bencode::encode($value);
        self::assertEquals($value, CallbackCombiner::parse(new CallbackDecoder(), $encoded));
    }

    public function testListDictList(): void
    {
        $value = [
            1,
            2,
            3 => [
                'a' => 'x',
                'b' => 'y',
                'c' => [
                    1,
                    2,
                    3,
                ],
                'd' => 'z',
            ],
            4,
        ];

        $encoded = Bencode::encode($value);
        self::assertEquals($value, CallbackCombiner::parse(new CallbackDecoder(), $encoded));
    }

    public function testListListList(): void
    {
        $value = [
            1,
            2,
            3 => [
                1,
                2,
                3 => [
                    1,
                    2,
                    3,
                ],
                4,
            ],
            4,
        ];

        $encoded = Bencode::encode($value);
        self::assertEquals($value, CallbackCombiner::parse(new CallbackDecoder(), $encoded));
    }

    public function testDictDictDict(): void
    {
        $value = [
            'a' => 'x',
            'b' => 'y',
            'c' => [
                'a' => 'x',
                'b' => 'y',
                'c' => [
                    'a' => 'x',
                    'b' => 'y',
                    'c' => 'z',
                ],
                'd' => 'z',
            ],
            'd' => 'z',
        ];

        $encoded = Bencode::encode($value);
        self::assertEquals($value, CallbackCombiner::parse(new CallbackDecoder(), $encoded));
    }
}
