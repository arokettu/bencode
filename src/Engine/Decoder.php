<?php

namespace SandFox\Bencode\Engine;

use SandFox\Bencode\Exceptions\InvalidArgumentException;
use SandFox\Bencode\Exceptions\ParseErrorException;
use SandFox\Bencode\Util\Util;

/**
 * Class Decoder
 * @package SandFox\Bencode\Engine
 * @author Anton Smirnov
 * @license MIT
 */
class Decoder
{
    private $bencoded;
    private $decoded;
    private $options;

    private $state;
    private $stateStack;
    private $index;
    private $eof;
    private $value;
    private $valueStack;

    const STATE_ROOT = 1;
    const STATE_LIST = 2;
    const STATE_DICT = 3;
    const STATE_INT  = 4;
    const STATE_STR  = 5;

    const DEFAULT_OPTIONS = [
        'listType' => 'array',
        'dictionaryType' => 'array',
    ];

    public function __construct(string $bencoded, array $options = [])
    {
        Util::detectMbstringOverload();

        $this->bencoded = $bencoded;
        $this->options = array_merge(self::DEFAULT_OPTIONS, $options);
    }

    public function decode()
    {
        $this->state        = self::STATE_ROOT;
        $this->stateStack   = [];
        $this->index        = 0;
        $this->eof          = strlen($this->bencoded);
        $this->decoded      = null;
        $this->valueStack   = [];

        while (!$this->eof()) {
            $this->processChar();
            $this->index += 1;
        }

        if ($this->state !== self::STATE_ROOT || $this->decoded === null) {
            throw new ParseErrorException('Unexpected end of file');
        }

        return $this->decoded;
    }

    private function processChar()
    {
        if ($this->stateContainer()) {
            // we're inside a container, find its children
            $this->nextObject();
        } else {
            switch ($this->state) {
                case self::STATE_INT:
                    $this->processInteger();
                    break;

                case self::STATE_STR:
                    $this->processString();
                    break;

                default:
                    // @codeCoverageIgnoreStart
                    // This exception means that we have a bug in our own code
                    throw new ParseErrorException('Parser entered invalid state while parsing char');
                    // @codeCoverageIgnoreEnd
            }
        }
    }

    private function stateContainer(): bool
    {
        return
            $this->state === self::STATE_ROOT ||
            $this->state === self::STATE_LIST ||
            $this->state === self::STATE_DICT;
    }

    private function nextObject()
    {
        if ($this->decoded !== null && $this->state === self::STATE_ROOT) {
            throw new ParseErrorException('Probably some junk after the end of the file');
        }

        switch ($this->char()) {
            case 'i':
                $this->push(self::STATE_INT);
                return;

            case 'l':
                $this->push(self::STATE_LIST);
                return;

            case 'd':
                $this->push(self::STATE_DICT);
                return;

            case 'e':
                $this->finalizeContainer();
                return;

            default:
                $this->push(self::STATE_STR);
                $this->value[] = $this->char();
        }
    }

    private function processInteger()
    {
        if ($this->char() === 'e') {
            $intStr = implode($this->value);
            $int    = intval($intStr);

            if (strval($int) !== $intStr) {
                throw new ParseErrorException("Invalid integer format or integer overflow: '{$intStr}'");
            }

            $this->pop($int);
        } else {
            $this->value[] = $this->char();
        }
    }

    private function processString()
    {
        if ($this->char() === ':') {
            $lenStr = implode($this->value);
            $len    = intval($lenStr);

            if (strval($len) !== $lenStr || $len < 0) {
                throw new ParseErrorException("Invalid string length value: '{$lenStr}'");
            }

            // we have length, just read all string here now

            $str = substr($this->bencoded, $this->index + 1, $len);
            $this->index += $len;

            if (strlen($str) !== $len) {
                throw new ParseErrorException('Unexpected end of file while processing string');
            }

            $this->pop($str);
        } else {
            $this->value[] = $this->char();
        }
    }

    private function finalizeContainer()
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

    private function finalizeList()
    {
        $value = $this->convertArrayToType($this->value, 'listType');

        $this->pop($value);
    }

    private function finalizeDict()
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
     * Push previous layer to the stack and set new state
     * @param int $newState
     */
    private function push(int $newState)
    {
        array_push($this->stateStack, $this->state);
        $this->state = $newState;

        if ($this->state !== self::STATE_ROOT) {
            array_push($this->valueStack, $this->value);
        }
        $this->value = [];
    }

    /**
     * Pop previous layer from the stack and give it a parsed value
     * @param mixed $valueToPrevLevel
     */
    private function pop($valueToPrevLevel)
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

    private function convertArrayToType(array $array, string $typeOption)
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
