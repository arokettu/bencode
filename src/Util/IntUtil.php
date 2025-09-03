<?php

/**
 * @copyright 2017 Anton Smirnov
 * @license MIT https://spdx.org/licenses/MIT.html
 */

declare(strict_types=1);

namespace Arokettu\Bencode\Util;

/**
 * @internal
 */
final class IntUtil
{
    public static function isValid(string $intStr): bool
    {
        return preg_match('/^(?:0|-?[1-9]\d*)$/', $intStr) === 1;
    }
}
