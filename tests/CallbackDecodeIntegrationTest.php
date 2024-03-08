<?php

declare(strict_types=1);

namespace Arokettu\Bencode\Tests;

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

        $decoded = CallbackCombiner::parse(
            new CallbackDecoder(),
            $bencode,
        );

        self::assertEquals($value, $decoded);
    }
}
