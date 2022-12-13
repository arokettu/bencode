<?php

declare(strict_types=1);

namespace SandFox\Bencode;

use SandFox\Bencode\Exceptions\FileNotWritableException;

final class Encoder
{
    private bool $useJsonSerializable;
    private bool $useStringable;

    public function __construct(
        array $options = [],
        bool $useJsonSerializable = false,
        bool $useStringable = false,
    ) {
        if ($options !== []) {
            trigger_deprecation(
                'arokettu/bencode',
                '3.1.0',
                '$options is deprecated, use named parameters',
            );
        }

        $this->useJsonSerializable = $options['useJsonSerializable'] ?? $useJsonSerializable;
        $this->useStringable = $options['useStringable'] ?? $useStringable;
    }

    /**
     * Dump data to bencoded stream
     *
     * @param mixed $data
     * @param resource|null $writeStream Write capable stream. If null, a new php://temp will be created
     * @return resource Original or created stream
     */
    public function encodeToStream(mixed $data, $writeStream = null)
    {
        if ($writeStream === null) {
            $writeStream = fopen('php://temp', 'r+');
        }

        return (new Engine\Encoder($data, $writeStream, $this->useJsonSerializable, $this->useStringable))->encode();
    }

    /**
     * Encode arbitrary data to bencoded string
     *
     * @param mixed $data
     * @return string
     */
    public function encode(mixed $data): string
    {
        $stream = fopen('php://temp', 'r+');
        $this->encodeToStream($data, $stream);
        rewind($stream);

        $encoded = stream_get_contents($stream);

        fclose($stream);

        return $encoded;
    }

    /**
     * Dump data to bencoded file
     *
     * @param mixed $data
     * @param string $filename
     * @return bool always true
     */
    public function dump(mixed $data, string $filename): bool
    {
        $writable = is_file($filename) ?
            is_writable($filename) :
            is_dir($dirname = dirname($filename)) && is_writable($dirname);

        if (!$writable) {
            throw new FileNotWritableException('The file is not writable: ' . $filename);
        }

        $stream = fopen($filename, 'w');

        if ($stream === false) {
            throw new FileNotWritableException('Error writing to file: ' . $filename); // @codeCoverageIgnore
        }

        $this->encodeToStream($data, $stream);

        $stat = fstat($stream);
        fclose($stream);

        return $stat['size'] > 0;
    }
}
