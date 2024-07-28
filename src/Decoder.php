<?php

declare(strict_types=1);

namespace Arokettu\Bencode;

use Arokettu\Bencode\Exceptions\FileNotReadableException;
use Closure;

final class Decoder
{
    /**
     * @internal
     */
    public const DEFAULT_LIST_TYPE = Bencode\Collection::ARRAY;
    /**
     * @internal
     */
    public const DEFAULT_DICT_TYPE = Bencode\Collection::ARRAY_OBJECT;
    /**
     * @internal
     */
    public const DEFAULT_BIG_INT = Bencode\BigInt::NONE;

    private readonly Closure $listHandler;
    private readonly Closure $dictHandler;
    private readonly Closure $bigIntHandler;

    /**
     * @param array $options No longer used
     * @param Bencode\Collection|callable $listType Type declaration for lists
     * @param Bencode\Collection|callable $dictType Type declaration for dictionaries
     * @param Bencode\BigInt|callable $bigInt Big integer mode
     */
    public function __construct(
        array $options = [],
        Bencode\Collection|callable $listType = self::DEFAULT_LIST_TYPE,
        Bencode\Collection|callable $dictType = self::DEFAULT_DICT_TYPE,
        Bencode\BigInt|callable $bigInt = self::DEFAULT_BIG_INT,
    ) {
        if ($options !== []) {
            throw new \BadFunctionCallException('$options array must not be used');
        }

        $this->listHandler = $listType instanceof Bencode\Collection ? $listType->getHandler() : $listType(...);
        $this->dictHandler = $dictType instanceof Bencode\Collection ? $dictType->getHandler() : $dictType(...);
        $this->bigIntHandler = $bigInt instanceof Bencode\BigInt ? $bigInt->getHandler() : $bigInt(...);
    }

    /**
     * Decode bencoded data from stream
     *
     * @param resource $readStream Read capable stream
     */
    public function decodeStream($readStream): mixed
    {
        return (new Engine\Reader(
            $readStream,
            $this->listHandler,
            $this->dictHandler,
            $this->bigIntHandler,
        ))->read();
    }

    /**
     * Decode bencoded data from string
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
     */
    public function load(string $filename): mixed
    {
        if (!is_file($filename) || !is_readable($filename)) {
            throw new FileNotReadableException('File does not exist or is not readable: ' . $filename);
        }

        $stream = fopen($filename, 'r');

        if ($stream === false) {
            throw new FileNotReadableException('Error reading file: ' . $filename); // @codeCoverageIgnore
        }

        $decoded = self::decodeStream($stream);

        fclose($stream);

        return $decoded;
    }
}
