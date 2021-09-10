<?php

declare(strict_types=1);

namespace SandFox\Bencode;

use SandFox\Bencode\Engine\Encoder;

/**
 * Class Bencode
 * @package SandFox\Bencode
 * @author Anton Smirnov
 * @license MIT
 */
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
     * @return string
     */
    public static function encode(mixed $data): string
    {
        $stream = fopen('php://temp', 'r+');
        self::encodeToStream($data, $stream);
        rewind($stream);

        $encoded = stream_get_contents($stream);

        fclose($stream);

        return $encoded;
    }

    /**
     * Dump data to bencoded stream
     *
     * @param mixed $data
     * @param resource|null $writeStream Write capable stream. If null, a new php://temp will be created
     * @return resource Original or created stream
     */
    public static function encodeToStream(mixed $data, $writeStream = null)
    {
        if ($writeStream === null) {
            $writeStream = fopen('php://temp', 'r+');
        }

        return (new Encoder($data, $writeStream))->encode();
    }

    /**
     * Dump data to bencoded file
     *
     * @param string $filename
     * @param mixed $data
     * @return bool success of file_put_contents
     */
    public static function dump(string $filename, mixed $data): bool
    {
        $stream = fopen($filename, 'w');

        if ($stream === false) {
            return false;
        }

        self::encodeToStream($data, $stream);

        $stat = fstat($stream);
        fclose($stream);

        return $stat['size'] > 0;
    }
}
