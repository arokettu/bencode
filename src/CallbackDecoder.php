<?php

declare(strict_types=1);

namespace Arokettu\Bencode;

use Arokettu\Bencode\Exceptions\FileNotReadableException;
use Closure;

final class CallbackDecoder
{
    /**
     * @internal
     */
    public const DEFAULT_BIG_INT = Bencode\BigInt::NONE;

    private readonly Closure $bigIntHandler;

    public function __construct(
        Bencode\BigInt|callable $bigInt = self::DEFAULT_BIG_INT,
    ) {
        $this->bigIntHandler = $bigInt instanceof Bencode\BigInt ? $bigInt->getHandler() : $bigInt(...);
    }

    /**
     * Decode bencoded data from stream
     *
     * @param resource $readStream Read capable stream
     */
    public function decodeStream($readStream, Types\CallbackHandler|callable $callback): void
    {
        (new Engine\CallbackReader(
            $readStream,
            $callback(...),
            $this->bigIntHandler,
        ))->read();
    }

    /**
     * Decode bencoded data from string
     *
     * @param string $bencoded
     */
    public function decode(string $bencoded, Types\CallbackHandler|callable $callback): void
    {
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $bencoded);
        rewind($stream);

        self::decodeStream($stream, $callback);

        fclose($stream);
    }

    /**
     * Load data from bencoded file
     */
    public function load(string $filename, Types\CallbackHandler|callable $callback): void
    {
        if (!is_file($filename) || !is_readable($filename)) {
            throw new FileNotReadableException('File does not exist or is not readable: ' . $filename);
        }

        $stream = fopen($filename, 'r');

        if ($stream === false) {
            throw new FileNotReadableException('Error reading file: ' . $filename); // @codeCoverageIgnore
        }

        self::decodeStream($stream, $callback);

        fclose($stream);
    }
}
