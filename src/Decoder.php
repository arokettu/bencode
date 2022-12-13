<?php

declare(strict_types=1);

namespace SandFox\Bencode;

use SandFox\Bencode\Exceptions\FileNotReadableException;
use SandFox\Bencode\Exceptions\InvalidArgumentException;

final class Decoder
{
    private \Closure $listHandler;
    private \Closure $dictHandler;
    private \Closure $bigIntHandler;

    public function __construct(
        array $options = [],
        Bencode\Collection|string|callable $listType = Bencode\Collection::ARRAY,
        Bencode\Collection|string|callable $dictType = Bencode\Collection::ARRAY,
        Bencode\BigInt|string|callable $bigInt = Bencode\BigInt::NONE,
    ) {
        if ($options !== []) {
            trigger_deprecation(
                'arokettu/bencode',
                '3.1.0',
                '$options is deprecated, use named parameters',
            );
        }

        $listType = $options['listType'] ?? $listType;
        $dictType = $options['dictType'] ?? $dictType;
        $bigInt   = $options['bigInt']   ?? $bigInt;

        $this->listHandler = match (true) {
            $listType instanceof Bencode\Collection,
                => $listType->getHandler(),
            is_callable($listType)
                => $listType(...),
            class_exists($listType)
                => $this->createClassClosure($listType),
            default
                => throw new InvalidArgumentException(
                    '$listType must be Bencode\Collection enum value, class name, or callback'
                ),
        };

        $this->dictHandler = match (true) {
            $dictType instanceof Bencode\Collection,
                => $dictType->getHandler(),
            is_callable($dictType)
                => $dictType(...),
            class_exists($dictType)
                => $this->createClassClosure($dictType),
            default
                => throw new InvalidArgumentException(
                    '$dictType must be Bencode\Collection enum value, class name, or callback'
                ),
        };

        $this->bigIntHandler = match (true) {
            $bigInt instanceof Bencode\BigInt,
                => $bigInt->getHandler(),
            is_callable($bigInt)
                => $bigInt(...),
            class_exists($bigInt)
                => $this->createClassClosure($bigInt),
            default
                => throw new InvalidArgumentException(
                    '$bigInt must be Bencode\BigInt enum value, class name, or callback'
                ),
        };
    }

    private function createClassClosure(string $class): \Closure
    {
        trigger_deprecation(
            'arokettu/bencode',
            '3.1.0',
            'Passing class names to listType, dictType, and bigInt is deprecated, use closures instead'
        );

        return fn ($value) => new $class($value);
    }

    /**
     * Decode bencoded data from stream
     *
     * @param resource $readStream Read capable stream
     */
    public function decodeStream($readStream): mixed
    {
        return (new Engine\Decoder(
            $readStream,
            $this->listHandler,
            $this->dictHandler,
            $this->bigIntHandler,
        ))->decode();
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
