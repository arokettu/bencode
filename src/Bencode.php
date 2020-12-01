<?php

namespace SandFox\Bencode;

use SandFox\Bencode\Engine\Decoder;
use SandFox\Bencode\Engine\Encoder;

/**
 * Class Bencode
 * @package SandFox\Bencode
 * @author Anton Smirnov
 * @license MIT
 */
class Bencode
{
    /**
     * Encode arbitrary data to bencode string
     *
     * @param mixed $data
     * @param array $options
     * @return string
     */
    public static function encode($data, array $options = []): string
    {
        $stream = fopen('php://temp', 'r+');
        (new Encoder($data, $stream))->encode();
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
     * @return mixed
     */
    public static function decode(string $bencoded, array $options = [])
    {
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $bencoded);
        rewind($stream);

        $decoded = self::decodeStream($stream, $options);

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
    public static function encodeToStream($data, $writeStream = null)
    {
        if ($writeStream === null) {
            $writeStream = fopen('php://temp', 'r+');
        }

        return (new Encoder($data, $writeStream))->encode();
    }

    /**
     * @param resource $readStream Read capable stream
     * @param array $options
     * @return mixed
     */
    public static function decodeStream($readStream, array $options = [])
    {
        if (isset($options['dictionaryType'])) {
            $options['dictType'] = $options['dictType'] ?? $options['dictionaryType'];
        }

        return (new Decoder($readStream, $options))->decode();
    }

    /**
     * Dump data to bencoded file
     *
     * @param string $filename
     * @param mixed $data
     * @param array $options
     * @return bool success of file_put_contents
     */
    public static function dump(string $filename, $data, array $options = []): bool
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
     * @return mixed
     */
    public static function load(string $filename, array $options = [])
    {
        $stream = fopen($filename, 'r');

        if ($stream === false) {
            return false;
        }

        $decoded = self::decodeStream($stream, $options);

        fclose($stream);

        return $decoded;
    }
}
