<?php

declare(strict_types=1);

namespace SandFox\Bencode;

final class Encoder
{
    public function __construct()
    {
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

        return (new Engine\Encoder($data, $writeStream))->encode();
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
     * @return bool success of file_put_contents
     */
    public function dump(mixed $data, string $filename): bool
    {
        $stream = fopen($filename, 'w');

        if ($stream === false) {
            return false;
        }

        $this->encodeToStream($data, $stream);

        $stat = fstat($stream);
        fclose($stream);

        return $stat['size'] > 0;
    }
}
