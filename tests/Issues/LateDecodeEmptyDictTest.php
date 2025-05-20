<?php

declare(strict_types=1);

namespace Arokettu\Bencode\Tests\Issues;

use Arokettu\Bencode\Bencode;
use PHPUnit\Framework\TestCase;

/**
 * The detected problem:
 * In case dict iterable is processed after the decoding,
 * all subsequent dictionaries are empty
 */
class LateDecodeEmptyDictTest extends TestCase
{
    public function testIssue(): void
    {
        $data = [
            'dict1' => [
                'key1' => 'value1',
                'key2' => 'value2',
            ],
            'dict2' => [
                'key3' => 'value3',
                'key4' => 'value4',
            ],
        ];

        $encoded = Bencode::encode($data);
        $prepareForJsonEncoding = Bencode::decode(
            $encoded,
            listType: fn ($v) => new LateDecodeEmptyDictTest\JsonList($v),
            dictType: fn ($v) => new LateDecodeEmptyDictTest\JsonDict($v),
        );
        $json = json_encode($prepareForJsonEncoding);
        $decoded = json_decode($json, true);

        self::assertEquals($data, $decoded);
    }
}
