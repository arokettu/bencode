<?php

/**
 * @copyright 2017 Anton Smirnov
 * @license MIT https://spdx.org/licenses/MIT.html
 */

declare(strict_types=1);

namespace Arokettu\Bencode\Tests\Helpers;

use Arokettu\Bencode\CallbackDecoder;
use Arokettu\Bencode\Types\CallbackHandler;

final class CallbackCombiner implements CallbackHandler
{
    public mixed $data = null;

    public static function decode(CallbackDecoder $decoder, string $bencoded): mixed
    {
        $cc = new self();
        $decoder->decode($bencoded, $cc);
        return $cc->data;
    }

    public static function load(CallbackDecoder $decoder, string $file): mixed
    {
        $cc = new self();
        $decoder->load($file, $cc);
        return $cc->data;
    }

    public function __invoke(array $keys, mixed $value): bool|null
    {
        $d = &$this->data;
        foreach ($keys as $key) {
            $d = &$d[$key];
        }
        $d = $value;

        return null;
    }
}
