<?php

declare(strict_types=1);

namespace SandFox\Bencode\Engine;

use SandFox\Bencode\Exceptions\InvalidArgumentException;
use SandFox\Bencode\Types\BencodeSerializable;
use SandFox\Bencode\Types\ListType;
use stdClass;
use Stringable;
use Traversable;

/**
 * Class Encoder
 * @package SandFox\Bencode\Engine
 * @author Anton Smirnov
 * @license MIT
 * @internal
 */
final class Encoder
{
    public function __construct(private mixed $data)
    {
    }

    public function encode(): string
    {
        return $this->encodeValue($this->data);
    }

    private function encodeValue(mixed $value): string
    {
        return match (true) {
            // first check if we have integer
            // boolean is converted to integer 1 or 0
            is_int($value), is_bool($value) =>
                $this->encodeInteger(intval($value)),
            // process strings
            // floats become strings
            // nulls become empty strings
            is_string($value), is_float($value), is_null($value) =>
                $this->encodeString(strval($value)),
            // process arrays
            is_array($value) =>
                $this->encodeArray($value),
            // process objects
            is_object($value) =>
                $this->encodeObject($value),
            // other types like resources
            default =>
                throw new InvalidArgumentException(
                    sprintf("Bencode doesn't know how to serialize an instance of %s", get_debug_type($value))
                ),
        };
    }

    private function encodeArray(array $value): string
    {
        return match ($this->isSequentialArray($value)) {
            true  => $this->encodeList($value),
            false => $this->encodeDictionary($value),
        };
    }

    private function encodeObject(object $value): string
    {
        return match (true) {
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
            $value instanceof Traversable, $value instanceof stdClass =>
                $this->encodeDictionary($value),
            // try to convert other objects to string
            $value instanceof Stringable =>
                $this->encodeString(strval($value)),
            // other classes
            default =>
                throw new InvalidArgumentException(
                    sprintf("Bencode doesn't know how to serialize an instance of %s", get_debug_type($value))
                ),
        };
    }

    private function encodeInteger(int $integer): string
    {
        return "i{$integer}e";
    }

    private function encodeString(string $string): string
    {
        $length = strlen($string);
        return "{$length}:$string";
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

    private function encodeDictionary(iterable|stdClass $array): string
    {
        $dictData = [];

        foreach ($array as $key => $value) {
            // do not use php array keys here to prevent numeric strings becoming integers again
            $dictData[] = [strval($key), $value];
        }

        // sort by keys - rfc requirement
        usort($dictData, fn($a, $b): int => strcmp($a[0], $b[0]));

        $dict = implode(array_map(function ($row): string {
            [$key, $value] = $row;
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
