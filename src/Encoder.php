<?php

declare(strict_types=1);

namespace Arokettu\Bencode;

use Arokettu\Bencode\Exceptions\FileNotWritableException;

final class Encoder
{
    /**
     * @param array $options No longer used
     */
    public function __construct(
        array $options = [],
        private readonly bool $useJsonSerializable = false,
        private readonly bool $useStringable = false,
    ) {
        if ($options !== []) {
            throw new \BadFunctionCallException('$options array must not be used');
        }
    }

    /**
     * Dump data to bencoded stream
     *
     * @param resource|null $writeStream Write capable stream. If null, a new php://temp will be created
     * @return resource Original or created stream
     */
    public function encodeToStream(mixed $data, $writeStream = null)
    {
        if ($writeStream === null) {
            $writeStream = fopen('php://temp', 'r+');
        }

        return (new Engine\Writer($data, $writeStream, $this->useJsonSerializable, $this->useStringable))->write();
    }

    /**
     * Encode arbitrary data to bencoded string
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
     * @return bool always true
     */
    public function dump(mixed $data, string $filename): bool
    {
        $writable = is_file($filename) ?
            is_writable($filename) :
            is_dir($dirname = \dirname($filename)) && is_writable($dirname);

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
