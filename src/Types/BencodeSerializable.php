<?php

/**
 * @copyright 2017 Anton Smirnov
 * @license MIT https://spdx.org/licenses/MIT.html
 */

declare(strict_types=1);

namespace Arokettu\Bencode\Types;

/**
 * @template T
 *
 * Objects implementing BencodeSerializable can customize their Bencode representation
 * when encoded with Bencode::encode()
 *
 * @see \JsonSerializable Similar concept for json_encode
 */
interface BencodeSerializable
{
    /**
     * Specify data which should be serialized to Bencode
     *
     * @return T
     */
    public function bencodeSerialize(): mixed;
}
