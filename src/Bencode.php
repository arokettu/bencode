<?php

declare(strict_types=1);

namespace Arokettu\Bencode;

/**
 * @psalm-api
 */
final class Bencode
{
    /**
     * Decode bencoded data from string
     *
     * @param array $options No longer used
     * @param Bencode\Collection|callable $listType Type declaration for lists
     * @param Bencode\Collection|callable $dictType Type declaration for dictionaries
     * @param Bencode\BigInt|callable $bigInt Big integer mode
     */
    public static function decode(
        string $bencoded,
        array $options = [],
        Bencode\Collection|callable $listType = Decoder::DEFAULT_LIST_TYPE,
        Bencode\Collection|callable $dictType = Decoder::DEFAULT_DICT_TYPE,
        Bencode\BigInt|callable $bigInt = Decoder::DEFAULT_BIG_INT,
    ): mixed {
        return (new Decoder($options, $listType, $dictType, $bigInt))->decode($bencoded);
    }

    /**
     * Decode bencoded data from stream
     *
     * @param resource $readStream Read capable stream
     * @param array $options No longer used
     * @param Bencode\Collection|callable $listType Type declaration for lists
     * @param Bencode\Collection|callable $dictType Type declaration for dictionaries
     * @param Bencode\BigInt|callable $bigInt Big integer mode
     */
    public static function decodeStream(
        $readStream,
        array $options = [],
        Bencode\Collection|callable $listType = Decoder::DEFAULT_LIST_TYPE,
        Bencode\Collection|callable $dictType = Decoder::DEFAULT_DICT_TYPE,
        Bencode\BigInt|callable $bigInt = Decoder::DEFAULT_BIG_INT,
    ): mixed {
        return (new Decoder($options, $listType, $dictType, $bigInt))->decodeStream($readStream);
    }

    /**
     * Load data from bencoded file
     *
     * @param array $options No longer used
     * @param Bencode\Collection|callable $listType Type declaration for lists
     * @param Bencode\Collection|callable $dictType Type declaration for dictionaries
     * @param Bencode\BigInt|callable $bigInt Big integer mode
     */
    public static function load(
        string $filename,
        array $options = [],
        Bencode\Collection|callable $listType = Decoder::DEFAULT_LIST_TYPE,
        Bencode\Collection|callable $dictType = Decoder::DEFAULT_DICT_TYPE,
        Bencode\BigInt|callable $bigInt = Decoder::DEFAULT_BIG_INT,
    ): mixed {
        return (new Decoder($options, $listType, $dictType, $bigInt))->load($filename);
    }

    /**
     * Encode arbitrary data to bencoded string
     *
     * @param array $options No longer used
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
     * @param resource|null $writeStream Write capable stream. If null, a new php://temp will be created
     * @param array $options No longer used
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
     * @param array $options No longer used
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
