<?php

declare(strict_types=1);

namespace SandFox\Bencode;

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
     * @param string|callable $listType Type declaration for lists
     * @param string|callable $dictType Type declaration for dictionaries
     * @param string|callable|null $dictionaryType Type declaration for dictionaries @deprecated
     * @param bool $useGMP Use GMP library for large integers @deprecated
     * @param string|callable $bigInt Big integer mode
     * @return mixed
     */
    public static function decode(
        string $bencoded,
        array $options = [],
        string|callable $listType = Bencode\Collection::ARRAY,
        string|callable $dictType = Bencode\Collection::ARRAY,
        string|callable|null $dictionaryType = null,
        bool $useGMP = false,
        string|callable $bigInt = Bencode\BigInt::NONE,
    ): mixed {
        return (new Decoder($options, $listType, $dictType, $dictionaryType, $useGMP, $bigInt))
            ->decode($bencoded);
    }

    /**
     * @param resource $readStream Read capable stream
     * @param array $options
     * @param string|callable $listType Type declaration for lists
     * @param string|callable $dictType Type declaration for dictionaries
     * @param string|callable|null $dictionaryType Type declaration for dictionaries @deprecated
     * @param bool $useGMP Use GMP library for large integers @deprecated
     * @param string|callable $bigInt Big integer mode
     * @return mixed
     */
    public static function decodeStream(
        $readStream,
        array $options = [],
        string|callable $listType = Bencode\Collection::ARRAY,
        string|callable $dictType = Bencode\Collection::ARRAY,
        string|callable|null $dictionaryType = null,
        bool $useGMP = false,
        string|callable $bigInt = Bencode\BigInt::NONE,
    ): mixed {
        return (new Decoder($options, $listType, $dictType, $dictionaryType, $useGMP, $bigInt))
            ->decodeStream($readStream);
    }

    /**
     * Load data from bencoded file
     *
     * @param string $filename
     * @param array $options
     * @param string|callable $listType Type declaration for lists
     * @param string|callable $dictType Type declaration for dictionaries
     * @param string|callable|null $dictionaryType Type declaration for dictionaries @deprecated
     * @param bool $useGMP Use GMP library for large integers @deprecated
     * @param string|callable $bigInt Big integer mode
     * @return mixed
     */
    public static function load(
        string $filename,
        array $options = [],
        string|callable $listType = Bencode\Collection::ARRAY,
        string|callable $dictType = Bencode\Collection::ARRAY,
        string|callable|null $dictionaryType = null,
        bool $useGMP = false,
        string|callable $bigInt = Bencode\BigInt::NONE,
    ): mixed {
        return (new Decoder($options, $listType, $dictType, $dictionaryType, $useGMP, $bigInt))
            ->load($filename);
    }

    /**
     * Encode arbitrary data to bencode string
     *
     * @param mixed $data
     * @param array $options
     * @param bool $useJsonSerializable
     * @return string
     */
    public static function encode(
        mixed $data,
        array $options = [],
        bool $useJsonSerializable = false,
    ): string {
        return (new Encoder($options, $useJsonSerializable))->encode($data);
    }

    /**
     * Dump data to bencoded stream
     *
     * @param mixed $data
     * @param null $writeStream Write capable stream. If null, a new php://temp will be created
     * @param array $options
     * @param bool $useJsonSerializable
     * @return resource Original or created stream
     */
    public static function encodeToStream(
        mixed $data,
        $writeStream = null,
        array $options = [],
        bool $useJsonSerializable = false,
    ) {
        return (new Encoder($options, $useJsonSerializable))->encodeToStream($data, $writeStream);
    }

    /**
     * Dump data to bencoded file
     *
     * @param string $filename
     * @param mixed $data
     * @param array $options
     * @param bool $useJsonSerializable
     * @return bool success of file_put_contents
     */
    public static function dump(
        string $filename,
        mixed $data,
        array $options = [],
        bool $useJsonSerializable = false,
    ): bool {
        return (new Encoder($options, $useJsonSerializable))->dump($data, $filename);
    }
}
