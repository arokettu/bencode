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
        return (new Encoder($data, $options))->encode();
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
        return (new Decoder($bencoded, $options))->decode();
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
        return file_put_contents($filename, self::encode($data, $options)) !== false;
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
        return self::decode(file_get_contents($filename), $options);
    }
}
