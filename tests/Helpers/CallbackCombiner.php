<?php

declare(strict_types=1);

namespace Arokettu\Bencode\Tests\Helpers;

use Arokettu\Bencode\CallbackDecoder;
use Arokettu\Bencode\Types\CallbackHandler;

class CallbackCombiner implements CallbackHandler
{
    public mixed $data = null;

    public static function parse(CallbackDecoder $decoder, string $bencoded): mixed
    {
        $cc = new self();
        $decoder->decode($bencoded, $cc(...));
        return $cc->data;
    }

    public function __invoke(array $keys, mixed $value): ?bool
    {
        $d = &$this->data;
        foreach ($keys as $key) {
            $d = &$d[$key];
        }
        $d = $value;

        return null;
    }
}
