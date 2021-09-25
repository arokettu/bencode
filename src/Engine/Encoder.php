<?php

declare(strict_types=1);

namespace SandFox\Bencode\Engine;

use Brick\Math\BigInteger;
use SandFox\Bencode\Exceptions\InvalidArgumentException;
use SandFox\Bencode\Types\BencodeSerializable;
use SandFox\Bencode\Types\BigIntType;
use SandFox\Bencode\Types\ListType;
use SandFox\Bencode\Util\Util;

use function Arokettu\IsResource\try_get_resource_type;

/**
 * Class Encoder
 * @package SandFox\Bencode\Engine
 * @author Anton Smirnov
 * @license MIT
 * @internal
 */
final class Encoder
{
    /** @var mixed */
    private $data;
    /** @var resource */
    private $stream;
    /** @var array */
    private $options;

    const DEFAULT_OPTIONS = [
        'useJsonSerializable' => false,
    ];

    public function __construct($data, $stream, array $options)
    {
        Util::detectMbstringOverload();

        $this->data = $data;
        $this->stream = $stream;
        $this->options = array_merge(self::DEFAULT_OPTIONS, $options);

        if (try_get_resource_type($this->stream) !== 'stream') {
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

    private function encodeValue($value)
    {
        switch (true) {
            case $value === false:
            case $value === null:
                throw new InvalidArgumentException('Unable to encode an empty value');

            // true is converted to integer 1
            case $value === true:
            case \is_int($value):
            case $value instanceof BigIntType:
            case $value instanceof \GMP:
            case $value instanceof BigInteger:
            case $value instanceof \Math_BigInteger:
                $this->encodeInteger($value);
                break;

            // process arrays
            case \is_array($value):
                $this->encodeArray($value);
                break;

            case \is_object($value):
                $this->encodeObject($value);
                break;

            // everything else is a string
            default:
                $this->encodeString($value);
        }
    }

    private function encodeArray(array $value)
    {
        if ($this->isSequentialArray($value)) {
            $this->encodeList($value);
        } else {
            $this->encodeDictionary($value);
        }
    }

    private function encodeObject($value)
    {
        switch (true) {
            // serializable
            case $value instanceof BencodeSerializable:
                // Start again with method result
                $this->encodeValue($value->bencodeSerialize());
                break;

            case $this->options['useJsonSerializable'] && $value instanceof \JsonSerializable:
                // Start again with method result
                $this->encodeValue($value->jsonSerialize());
                break;

            // traversables
            case $value instanceof ListType:
                // ListType forces traversable object to be list
                $this->encodeList($value);
                break;

            // all other traversables are dictionaries
            // also treat stdClass as a dictionary
            case $value instanceof \Traversable:
            case $value instanceof \stdClass:
                $this->encodeDictionary($value);
                break;

            // try to convert other objects to string
            default:
                $this->encodeString($value);
        }
    }

    private function encodeInteger($integer)
    {
        fwrite($this->stream, 'i');
        fwrite($this->stream, (string)$integer);
        fwrite($this->stream, 'e');
    }

    private function encodeString($string)
    {
        $string = (string)$string;

        fwrite($this->stream, (string)\strlen($string));
        fwrite($this->stream, ':');
        fwrite($this->stream, $string);
    }

    private function encodeList($array)
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

    private function encodeDictionary($array)
    {
        $dictData = [];

        foreach ($array as $key => $value) {
            if ($value === false || $value === null) {
                continue;
            }

            // do not use php array keys here to prevent numeric strings becoming integers again
            $dictData[] = [\strval($key), $value];
        }

        // sort by keys - rfc requirement
        usort($dictData, function ($a, $b) {
            if ($a[0] === $b[0]) {
                throw new InvalidArgumentException("Dictionary contains repeated keys: '{$a[0]}'");
            }

            return strcmp($a[0], $b[0]);
        });

        fwrite($this->stream, 'd');

        foreach ($dictData as list($key, $value)) {
            $this->encodeString($key); // key is always a string
            $this->encodeValue($value);
        }

        fwrite($this->stream, 'e');
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
