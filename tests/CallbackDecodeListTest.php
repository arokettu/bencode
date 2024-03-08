<?php

declare(strict_types=1);

// phpcs:disable Generic.Functions.FunctionCallArgumentSpacing.TooMuchSpaceAfterComma
// phpcs:disable Generic.Files.LineLength.TooLong

namespace Arokettu\Bencode\Tests;

use Arokettu\Bencode\CallbackDecoder;
use Arokettu\Bencode\Tests\Helpers\CallbackCombiner;
use PHPUnit\Framework\TestCase;

class CallbackDecodeListTest extends TestCase
{
    public function testListSimple(): void
    {
        // of integers
        self::assertEquals([2, 3, 5, 7, 11, 13], CallbackCombiner::parse(new CallbackDecoder(), 'li2ei3ei5ei7ei11ei13ee'));
        // of strings
        self::assertEquals(['s1', 's2'], CallbackCombiner::parse(new CallbackDecoder(), 'l2:s12:s2e'));
        // mixed
        self::assertEquals([2, 's1', 3, 's2', 5], CallbackCombiner::parse(new CallbackDecoder(), 'li2e2:s1i3e2:s2i5ee'));
        // empty
        self::assertEquals(null, CallbackCombiner::parse(new CallbackDecoder(), 'le'));
    }

    public function testListTypes(): void
    {
        $list       = [2, 's1', 3, 's2', 5];
        $encoded    = 'li2e2:s1i3e2:s2i5ee';

        // array
        $decodedArray = CallbackCombiner::parse(new CallbackDecoder(), $encoded);

        self::assertEquals($list, $decodedArray);
    }
}
