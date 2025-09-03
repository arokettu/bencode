<?php

/**
 * @copyright 2017 Anton Smirnov
 * @license MIT https://spdx.org/licenses/MIT.html
 */

declare(strict_types=1);

namespace Arokettu\Bencode\Tests;

use Arokettu\Bencode\Bencode;
use PHPUnit\Framework\TestCase;

final class OptionsArrayTest extends TestCase
{
    public function testDecoder(): void
    {
        $this->expectException(\BadFunctionCallException::class);
        $this->expectExceptionMessage('$options array must not be used');

        Bencode::decode('de', ['any' => 'thing']);
    }

    public function testEncoder(): void
    {
        $this->expectException(\BadFunctionCallException::class);
        $this->expectExceptionMessage('$options array must not be used');

        Bencode::encode([], ['any' => 'thing']);
    }
}
