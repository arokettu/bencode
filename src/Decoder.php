<?php

declare(strict_types=1);

namespace SandFox\Bencode;

final class Decoder
{
    private array $options;

    public function __construct(
        array $options = [],
        string|callable $listType = Bencode\Collection::ARRAY,
        string|callable $dictType = Bencode\Collection::ARRAY,
        string|callable|null $dictionaryType = null,
        bool $useGMP = false,
        string|callable $bigInt = Bencode\BigInt::NONE,
    ) {
        // deprecations
        if (isset($dictionaryType) || isset($options['dictionaryType'])) {
            trigger_deprecation(
                'sandfoxme/bencode',
                '2.3.0',
                'dictionaryType option is deprecated, use dictType instead',
            );
        }

        if ($useGMP || isset($options['useGMP'])) {
            trigger_deprecation(
                'sandfoxme/bencode',
                '2.7.0',
                'useGMP option is deprecated, use bigInt => Bencode\BigInt::GMP instead',
            );
        }

        // resolve dictType / dictionaryType alias
        if (isset($dictionaryType)) {
            $dictType = $dictionaryType;
        }

        // resolve useGMP
        if ($useGMP) {
            $bigInt = Bencode\BigInt::GMP;
        }

        if (\count($options) > 0) {
            // resolve dictType / dictionaryType alias
            if (isset($options['dictionaryType'])) {
                $options['dictType'] ??= $options['dictionaryType'];
                unset($options['dictionaryType']);
            }

            // resolve useGMP
            if (isset($options['useGMP'])) {
                $options['bigInt'] ??= Bencode\BigInt::GMP;
                unset($options['useGMP']);
            }
        }

        $this->options = array_merge(compact('listType', 'dictType', 'bigInt'), $options);
    }

    /**
     * Decode bencoded data from stream
     *
     * @param resource $readStream Read capable stream
     */
    public function decodeStream($readStream): mixed
    {
        return (new Engine\Decoder($readStream, ...$this->options))->decode();
    }

    /**
     * Decode bencoded data from string
     *
     * @param string $bencoded
     * @return mixed
     */
    public function decode(string $bencoded): mixed
    {
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $bencoded);
        rewind($stream);

        $decoded = self::decodeStream($stream);

        fclose($stream);

        return $decoded;
    }

    /**
     * Load data from bencoded file
     *
     * @param string $filename
     * @return mixed
     */
    public function load(string $filename): mixed
    {
        $stream = fopen($filename, 'r');

        if ($stream === false) {
            return false;
        }

        $decoded = self::decodeStream($stream);

        fclose($stream);

        return $decoded;
    }
}
