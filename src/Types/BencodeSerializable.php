<?php

declare(strict_types=1);

namespace Arokettu\Bencode\Types;

/**
 * Interface BencodeSerializable
 * @package SandFox\Bencode\Types
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
     */
    public function bencodeSerialize(): mixed;
}
