<?php

declare(strict_types=1);

namespace SandFox\Bencode\Engine;

use SandFox\Bencode\Exceptions\InvalidArgumentException;
use SandFox\Bencode\Types\BencodeSerializable;
use SandFox\Bencode\Types\ListType;

/**
 * Class Encoder
 * @package SandFox\Bencode\Engine
 * @author Anton Smirnov
 * @license MIT
 */
class Encoder
{
    public function __construct(
        private mixed $data,
        private array $options = [],
    ) {
    }

    public function encode(): string
    {
        return $this->encodeValue($this->data);
    }

    private function encodeValue(mixed $value): string
    {
        // first check if we have integer
        // boolean is converted to integer 1 or 0
        if (is_int($value) || is_bool($value)) {
            return $this->encodeInteger(intval($value));
        }

        // process strings
        // floats become strings
        if (is_string($value) || is_float($value) || is_null($value)) {
            return $this->encodeString(strval($value));
        }

        // process arrays
        if (is_array($value)) {
            return $this->encodeArray($value);
        }

        if (is_object($value)) {
            return $this->encodeObject($value);
        }

        throw new InvalidArgumentException(
            sprintf("Bencode doesn't know how to serialize an instance of %s", get_debug_type($value))
        );
    }

    private function encodeArray(array $value): string
    {
        if ($this->isSequentialArray($value)) {
            return $this->encodeList($value);
        } else {
            return $this->encodeDictionary($value);
        }
    }

    private function encodeObject(object $value): string
    {
        // serializable
        if ($value instanceof BencodeSerializable) {
            // Start again with method result
            return $this->encodeValue($value->bencodeSerialize());
        }

        // traversables
        if ($value instanceof ListType) {
            // ListType forces traversable object to be list
            return $this->encodeList($value);
        }

        // all other traversables are dictionaries
        // also treat stdClass as a dictionary
        if ($value instanceof \Traversable || $value instanceof \stdClass) {
            return $this->encodeDictionary($value);
        }

        // try to convert other objects to string
        if ($value instanceof \Stringable) {
            return $this->encodeString(strval($value));
        }

        throw new InvalidArgumentException(
            sprintf("Bencode doesn't know how to serialize an instance of %s", get_debug_type($value))
        );
    }

    private function encodeInteger(int $integer): string
    {
        return "i{$integer}e";
    }

    private function encodeString(string $string): string
    {
        return implode([strlen($string), ':', $string]);
    }

    private function encodeList(iterable $array): string
    {
        $listData = [];

        foreach ($array as $value) {
            $listData[] = $this->encodeValue($value);
        }

        $list = implode($listData);

        return "l{$list}e";
    }

    private function encodeDictionary(iterable|\stdClass $array): string
    {
        $dictData = [];

        foreach ($array as $key => $value) {
            // do not use php array keys here to prevent numeric strings becoming integers again
            $dictData[] = [strval($key), $value];
        }

        // sort by keys - rfc requirement
        usort($dictData, function ($a, $b) {
            return strcmp($a[0], $b[0]);
        });

        $dict = implode(array_map(function ($row) {
            list($key, $value) = $row;
            return $this->encodeString($key) . $this->encodeValue($value); // key is always a string
        }, $dictData));

        return "d{$dict}e";
    }

    private function isSequentialArray(array $array): bool
    {
        $index = 0;

        foreach ($array as $key => $value) {
            if ($key !== $index) {
                return false;
            }

            $index += 1;
        }

        return true;
    }
}
