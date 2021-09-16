<?php

declare(strict_types=1);

namespace SandFox\Bencode;

final class Bencode
{
    /**
     * Decode bencoded data from string
     *
     * @param string $bencoded
     * @param array $options
     * @param Bencode\Collection|string|callable $listType Type declaration for lists
     * @param Bencode\Collection|string|callable $dictType Type declaration for dictionaries
     * @param Bencode\BigInt|string|callable $bigInt Big integer mode
     * @return mixed
     */
    public static function decode(
        string $bencoded,
        array $options = [],
        Bencode\Collection|string|callable $listType = Bencode\Collection::ARRAY,
        Bencode\Collection|string|callable $dictType = Bencode\Collection::ARRAY,
        Bencode\BigInt|string|callable $bigInt = Bencode\BigInt::NONE,
    ): mixed {
        return (new Decoder($options, $listType, $dictType, $bigInt))->decode($bencoded);
    }

    /**
     * Decode bencoded data from stream
     *
     * @param resource $readStream Read capable stream
     * @param array $options
     * @param Bencode\Collection|string|callable $listType Type declaration for lists
     * @param Bencode\Collection|string|callable $dictType Type declaration for dictionaries
     * @param Bencode\BigInt|string|callable $bigInt Big integer mode
     * @return mixed
     */
    public static function decodeStream(
        $readStream,
        array $options = [],
        Bencode\Collection|string|callable $listType = Bencode\Collection::ARRAY,
        Bencode\Collection|string|callable $dictType = Bencode\Collection::ARRAY,
        Bencode\BigInt|string|callable $bigInt = Bencode\BigInt::NONE,
    ): mixed {
        return (new Decoder($options, $listType, $dictType, $bigInt))->decodeStream($readStream);
    }

    /**
     * Load data from bencoded file
     *
     * @param string $filename
     * @param array $options
     * @param Bencode\Collection|string|callable $listType Type declaration for lists
     * @param Bencode\Collection|string|callable $dictType Type declaration for dictionaries
     * @param Bencode\BigInt|string|callable $bigInt Big integer mode
     * @return mixed
     */
    public static function load(
        string $filename,
        array $options = [],
        Bencode\Collection|string|callable $listType = Bencode\Collection::ARRAY,
        Bencode\Collection|string|callable $dictType = Bencode\Collection::ARRAY,
        Bencode\BigInt|string|callable $bigInt = Bencode\BigInt::NONE,
    ): mixed {
        return (new Decoder($options, $listType, $dictType, $bigInt))->load($filename);
    }

    /**
     * Encode arbitrary data to bencoded string
     *
     * @param mixed $data
     * @param array $options
     * @param bool $useJsonSerializable
     * @param bool $useStringable
     * @return string
     */
    public static function encode(
        mixed $data,
        array $options = [],
        bool $useJsonSerializable = false,
        bool $useStringable = false,
    ): string {
        return (new Encoder($options, $useJsonSerializable, $useStringable))->encode($data);
    }

    /**
     * Dump data to bencoded stream
     *
     * @param mixed $data
     * @param null $writeStream Write capable stream. If null, a new php://temp will be created
     * @param array $options
     * @param bool $useJsonSerializable
     * @param bool $useStringable
     * @return resource Original or created stream
     */
    public static function encodeToStream(
        mixed $data,
        $writeStream = null,
        array $options = [],
        bool $useJsonSerializable = false,
        bool $useStringable = false,
    ) {
        return (new Encoder($options, $useJsonSerializable, $useStringable))->encodeToStream($data, $writeStream);
    }

    /**
     * Dump data to bencoded file
     *
     * @param mixed $data
     * @param string $filename
     * @param array $options
     * @param bool $useJsonSerializable
     * @param bool $useStringable
     * @return bool success of file_put_contents
     */
    public static function dump(
        mixed $data,
        string $filename,
        array $options = [],
        bool $useJsonSerializable = false,
        bool $useStringable = false,
    ): bool {
        return (new Encoder($options, $useJsonSerializable, $useStringable))->dump($data, $filename);
    }
}
