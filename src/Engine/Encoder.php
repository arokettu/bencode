<?php

/**
 * @noinspection PhpVoidFunctionResultUsedInspection
 */

declare(strict_types=1);

namespace SandFox\Bencode\Engine;

use Brick\Math\BigInteger;
use SandFox\Bencode\Exceptions\InvalidArgumentException;
use SandFox\Bencode\Types\BencodeSerializable;
use SandFox\Bencode\Types\BigIntType;
use SandFox\Bencode\Types\ListType;

/**
 * Class Encoder
 * @package SandFox\Bencode\Engine
 * @author Anton Smirnov
 * @license MIT
 * @internal
 */
final class Encoder
{
    public function __construct(private mixed $data, private $stream)
    {
        if (!is_resource($this->stream) || get_resource_type($this->stream) !== 'stream') {
            throw new InvalidArgumentException('Output is not a valid stream');
        }
    }

    /**
     * @return resource
     */
    public function encode()
    {
        $this->encodeValue($this->data);

        return $this->stream;
    }

    private function encodeValue(mixed $value): void
    {
        match (true) {
            // first check if we have integer
            // true is converted to integer 1
            is_int($value),
            $value === true,
            $value instanceof BigIntType,
            $value instanceof \GMP,
            $value instanceof BigInteger,
            $value instanceof \Math_BigInteger,
                => $this->encodeInteger($value),
            // process strings
            // floats become strings
            // nulls become empty strings
            is_string($value),
            is_float($value),
                => $this->encodeString((string)$value),
            // process arrays
            is_array($value) => $this->encodeArray($value),
            // process objects
            is_object($value) => $this->encodeObject($value),
            // empty values
            $value === false,
            $value === null,
                => throw new InvalidArgumentException('Unable to encode an empty value'),
            // other types like resources
            default
                => throw new InvalidArgumentException(
                    sprintf("Bencode doesn't know how to serialize an instance of %s", get_debug_type($value))
                ),
        };
    }

    private function encodeArray(array $value): void
    {
        match (array_is_list($value)) {
            true  => $this->encodeList($value),
            false => $this->encodeDictionary($value),
        };
    }

    private function encodeObject(object $value): void
    {
        match (true) {
            // serializable
            // Start again with method result
            $value instanceof BencodeSerializable =>
                $this->encodeValue($value->bencodeSerialize()),
            // traversables
            // ListType forces traversable object to be list
            $value instanceof ListType =>
                $this->encodeList($value),
            // all other traversables are dictionaries
            // also treat stdClass as a dictionary
            $value instanceof \Traversable, $value instanceof \stdClass =>
                $this->encodeDictionary($value),
            // try to convert other objects to string
            $value instanceof \Stringable =>
                $this->encodeString((string)$value),
            // other classes
            default =>
                throw new InvalidArgumentException(
                    sprintf("Bencode doesn't know how to serialize an instance of %s", get_debug_type($value))
                ),
        };
    }

    private function encodeInteger(int|bool|BigIntType|\GMP|BigInteger|\Math_BigInteger $integer)
    {
        fwrite($this->stream, 'i');
        fwrite($this->stream, (string)$integer);
        fwrite($this->stream, 'e');
    }

    private function encodeString(string $string): void
    {
        fwrite($this->stream, (string)strlen($string));
        fwrite($this->stream, ':');
        fwrite($this->stream, $string);
    }

    private function encodeList(iterable $array): void
    {
        fwrite($this->stream, 'l');

        foreach ($array as $value) {
            if ($value === false || $value === null) {
                continue;
            }

            $this->encodeValue($value);
        }

        fwrite($this->stream, 'e');
    }

    private function encodeDictionary(iterable|\stdClass $array): void
    {
        $dictData = [];

        foreach ($array as $key => $value) {
            if ($value === false || $value === null) {
                continue;
            }

            // do not use php array keys here to prevent numeric strings becoming integers again
            $dictData[] = [(string)$key, $value];
        }

        // sort by keys - rfc requirement
        usort($dictData, fn($a, $b): int => strcmp($a[0], $b[0]));

        fwrite($this->stream, 'd');

        foreach ($dictData as [$key, $value]) {
            $this->encodeString($key); // key is always a string
            $this->encodeValue($value);
        }

        fwrite($this->stream, 'e');
    }
}
