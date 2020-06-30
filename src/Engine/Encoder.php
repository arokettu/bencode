<?php

namespace SandFox\Bencode\Engine;

use SandFox\Bencode\Types\BencodeSerializable;
use SandFox\Bencode\Types\ListType;
use SandFox\Bencode\Util\Util;

/**
 * Class Encoder
 * @package SandFox\Bencode\Engine
 * @author Anton Smirnov
 * @license MIT
 */
class Encoder
{
    private $data;

    public function __construct($data, array $options = [])
    {
        Util::detectMbstringOverload();

        $this->data = $data;
    }

    public function encode(): string
    {
        return $this->encodeValue($this->data);
    }

    private function encodeValue($value): string
    {
        // first check if we have integer
        // boolean is converted to integer 1 or 0
        if (is_int($value) || is_bool($value)) {
            return $this->encodeInteger($value);
        }

        // process arrays
        if (is_array($value)) {
            return $this->encodeArray($value);
        }

        if (is_object($value)) {
            return $this->encodeObject($value);
        }

        // everything else is a string
        return $this->encodeString($value);
    }

    private function encodeArray(array $value): string
    {
        if ($this->isSequentialArray($value)) {
            return $this->encodeList($value);
        } else {
            return $this->encodeDictionary($value);
        }
    }

    private function encodeObject($value): string
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
        return $this->encodeString($value);
    }

    private function encodeInteger(int $integer): string
    {
        return "i{$integer}e";
    }

    private function encodeString(string $string): string
    {
        return implode([strlen($string), ':', $string]);
    }

    private function encodeList($array): string
    {
        $listData = [];

        foreach ($array as $value) {
            $listData[] = $this->encodeValue($value);
        }

        $list = implode($listData);

        return "l{$list}e";
    }

    private function encodeDictionary($array): string
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
