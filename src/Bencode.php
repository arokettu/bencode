<?php

declare(strict_types=1);

namespace SandFox\Bencode;

use SandFox\Bencode\Engine\Decoder;
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
     * Encode arbitrary data to bencode string
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
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $bencoded);
        rewind($stream);

        $decoded = self::decodeStream($stream, $options, $listType, $dictType, $dictionaryType, $useGMP, $bigInt);

        fclose($stream);

        return $decoded;
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
        // resolve dictType / dictionaryType alias
        if (isset($dictionaryType)) {
            trigger_deprecation(
                'sandfoxme/bencode',
                '2.3.0',
                'dictionaryType option is deprecated, use dictType instead',
            );
            $dictType = $dictionaryType;
        }

        if (count($options) > 0) {
            if (isset($options['dictionaryType'])) {
                $options['dictType'] ??= $options['dictionaryType'];
                unset($options['dictionaryType']);
            }
        }

        if ($useGMP) {
            $bigInt = Bencode\BigInt::GMP;
        }

        $options = array_merge(compact('listType', 'dictType', 'bigInt'), $options);

        return (new Decoder($readStream, ...$options))->decode();
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
        $stream = fopen($filename, 'r');

        if ($stream === false) {
            return false;
        }

        $decoded = self::decodeStream($stream, $options, $listType, $dictType, $dictionaryType, $useGMP, $bigInt);

        fclose($stream);

        return $decoded;
    }
}
