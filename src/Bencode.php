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
        return (new Encoder($data))->encode();
    }

    /**
     * Decode bencoded data from string
     *
     * @param string $bencoded
     * @param array $options @deprecated
     * @param string|callable $listType Type declaration for lists
     * @param string|callable $dictType Type declaration for dictionaries
     * @param string|callable|null $dictionaryType Type declaration for dictionaries @deprecated
     * @return mixed
     */
    public static function decode(
        string $bencoded,
        array $options = [],
        string|callable $listType = 'array',
        string|callable $dictType = 'array',
        string|callable|null $dictionaryType = null,
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
            trigger_deprecation(
                'sandfoxme/bencode',
                '2.1.0',
                'Using options array is deprecated, use named function parameters instead',
            );

            if (isset($options['dictionaryType'])) {
                $options['dictType'] ??= $options['dictionaryType'];
                unset($options['dictionaryType']);
            }
        }

        $options = array_merge(compact('listType', 'dictType'), $options);

        return (new Decoder($bencoded, ...$options))->decode();
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
        return file_put_contents($filename, self::encode($data)) !== false;
    }

    /**
     * Load data from bencoded file
     *
     * @param string $filename
     * @param array $options @deprecated
     * @param string|callable $listType Type declaration for lists
     * @param string|callable $dictType Type declaration for dictionaries
     * @param string|callable|null $dictionaryType Type declaration for dictionaries @deprecated
     * @return mixed
     */
    public static function load(
        string $filename,
        array $options = [],
        string|callable $listType = 'array',
        string|callable $dictType = 'array',
        string|callable|null $dictionaryType = null,
    ): mixed {
        return self::decode(file_get_contents($filename), $options, $listType, $dictType, $dictionaryType);
    }
}
