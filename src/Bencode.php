<?php

namespace SandFoxMe\Bencode;

use SandFoxMe\Bencode\Engine\Decoder;
use SandFoxMe\Bencode\Engine\Encoder;

/**
 * Class Bencode
 * @package SandFoxMe\Bencode
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
     */
    public static function dump(string $filename, $data, array $options = [])
    {
        file_put_contents($filename, self::encode($data, $options));
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
