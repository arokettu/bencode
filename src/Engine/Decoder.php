<?php

declare(strict_types=1);

namespace SandFox\Bencode\Engine;

use SandFox\Bencode\Exceptions\InvalidArgumentException;
use SandFox\Bencode\Exceptions\ParseErrorException;

/**
 * Class Decoder
 * @package SandFox\Bencode\Engine
 * @author Anton Smirnov
 * @license MIT
 */
final class Decoder
{
    private mixed $decoded;

    private int $state;
    private array $stateStack;
    private int $index;
    private int $eof;
    private mixed $value;
    private array $valueStack;

    private const STATE_ROOT = 1;
    private const STATE_LIST = 2;
    private const STATE_DICT = 3;

    public const DEFAULT_OPTIONS = [
        'listType' => 'array',
        'dictionaryType' => 'array',
    ];

    public function __construct(
        private string $bencoded,
        private array $options = [],
    ) {
        $this->options = array_merge(self::DEFAULT_OPTIONS, $this->options);
    }

    public function decode(): mixed
    {
        $this->state        = self::STATE_ROOT;
        $this->stateStack   = [];
        $this->index        = 0;
        $this->eof          = strlen($this->bencoded);
        $this->decoded      = null;
        $this->value        = null;
        $this->valueStack   = [];

        while (!$this->eof()) {
            $this->processChar();
        }

        if ($this->state !== self::STATE_ROOT || $this->decoded === null) {
            throw new ParseErrorException('Unexpected end of file');
        }

        return $this->decoded;
    }

    private function processChar(): void
    {
        if ($this->decoded !== null && $this->state === self::STATE_ROOT) {
            throw new ParseErrorException('Probably some junk after the end of the file');
        }

        switch ($this->char()) {
            case 'i':
                $this->processInteger();
                return;

            case 'l':
                $this->push(self::STATE_LIST);
                $this->index += 1; // skip l
                return;

            case 'd':
                $this->push(self::STATE_DICT);
                $this->index += 1; // skip d
                return;

            case 'e':
                $this->finalizeContainer();
                $this->index += 1; // skip e
                return;

            default:
                $this->processString();
        }
    }

    private function processInteger(): void
    {
        $this->index += 1; // skip 'i'

        $intEndIndex = strpos($this->bencoded, 'e', $this->index);

        if ($intEndIndex === false) {
            throw new ParseErrorException("Unexpected end of file while processing integer");
        }

        $intStr = substr($this->bencoded, $this->index, $intEndIndex - $this->index);
        $int    = intval($intStr);

        if (strval($int) !== $intStr) {
            throw new ParseErrorException("Invalid integer format or integer overflow: '{$intStr}'");
        }

        $this->index += strlen($intStr);
        $this->index += 1; // skip 'e'

        $this->finalizeScalar($int);
    }

    private function processString(): void
    {
        $lenEndIndex = strpos($this->bencoded, ':', $this->index);

        if ($lenEndIndex === false) {
            throw new ParseErrorException('Unexpected end of file while processing string');
        }

        $lenStr = substr($this->bencoded, $this->index, $lenEndIndex - $this->index);
        $len    = intval($lenStr);

        $this->index += strlen($lenStr);
        $this->index += 1; // skip ':'

        if (strval($len) !== $lenStr || $len < 0) {
            throw new ParseErrorException("Invalid string length value: '{$lenStr}'");
        }

        // we have length, just read all string here now

        $str = substr($this->bencoded, $this->index, $len);
        $this->index += $len;

        if (strlen($str) !== $len) {
            throw new ParseErrorException('Unexpected end of file while processing string');
        }

        $this->finalizeScalar($str);
    }

    private function finalizeContainer(): void
    {
        switch ($this->state) {
            case self::STATE_LIST:
                $this->finalizeList();
                break;

            case self::STATE_DICT:
                $this->finalizeDict();
                break;

            default:
                // @codeCoverageIgnoreStart
                // This exception means that we have a bug in our own code
                throw new ParseErrorException('Parser entered invalid state while finalizing container');
                // @codeCoverageIgnoreEnd
        }
    }

    private function finalizeList(): void
    {
        $value = $this->convertArrayToType($this->value, 'listType');

        $this->pop($value);
    }

    private function finalizeDict(): void
    {
        $dict = [];

        $prevKey = null;

        // we have an array [key1, value1, key2, value2, key3, value3, ...]
        while (count($this->value)) {
            $dictKey = array_shift($this->value);
            if (is_string($dictKey) === false) {
                throw new ParseErrorException('Non string key found in the dictionary');
            }
            if (count($this->value) === 0) {
                throw new ParseErrorException("Dictionary key without corresponding value: '{$dictKey}'");
            }
            if ($prevKey && strcmp($prevKey, $dictKey) >= 0) {
                throw new ParseErrorException("Invalid order of dictionary keys: '{$dictKey}' after '{$prevKey}'");
            }
            $dictValue = array_shift($this->value);

            $dict[$dictKey] = $dictValue;
            $prevKey = $dictKey;
        }

        $value = $this->convertArrayToType($dict, 'dictionaryType');

        $this->pop($value);
    }

    /**
     * Send parsed value to the current container
     * @param mixed $value
     */
    private function finalizeScalar(mixed $value): void
    {
        if ($this->state !== self::STATE_ROOT) {
            $this->value[] = $value;
        } else {
            // we have final result
            $this->decoded = $value;
        }
    }

    /**
     * Push previous layer to the stack and set new state
     * @param int $newState
     */
    private function push(int $newState): void
    {
        array_push($this->stateStack, $this->state);
        $this->state = $newState;

        array_push($this->valueStack, $this->value);
        $this->value = [];
    }

    /**
     * Pop previous layer from the stack and give it a parsed value
     * @param mixed $valueToPrevLevel
     */
    private function pop(mixed $valueToPrevLevel): void
    {
        $this->state = array_pop($this->stateStack);

        if ($this->state !== self::STATE_ROOT) {
            $this->value = array_pop($this->valueStack);
            $this->value[] = $valueToPrevLevel;
        } else {
            // we have final result
            $this->decoded = $valueToPrevLevel;
        }
    }

    private function char(): string
    {
        return $this->bencoded[$this->index];
    }

    private function eof(): bool
    {
        return $this->index === $this->eof;
    }

    private function convertArrayToType(array $array, string $typeOption): mixed
    {
        $type = $this->options[$typeOption];

        if ($type === 'array') {
            return $array;
        }

        if ($type === 'object') {
            return (object)$array;
        }

        if (is_callable($type)) {
            return call_user_func($type, $array);
        }

        if (class_exists($type)) {
            return new $type($array);
        }

        throw new InvalidArgumentException(
            "Invalid type option for '{$typeOption}'. Type should be 'array', 'object', class name, or callback"
        );
    }
}
