<?php

namespace SandFoxMe\Bencode\Engine;

use SandFoxMe\Bencode\Exceptions\InvalidArgumentException;
use SandFoxMe\Bencode\Exceptions\ParseErrorException;

/**
 * Class Decoder
 * @package SandFoxMe\Bencode\Engine
 * @author Anton Smirnov
 * @license MIT
 */
class Decoder
{
    private $bencoded;
    private $decoded;
    private $options = [];

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
        $this->bencoded = str_split($bencoded);
        $this->options = array_merge(self::DEFAULT_OPTIONS, $options);
    }

    public function decode()
    {
        $this->state        = self::STATE_ROOT;
        $this->stateStack   = [];
        $this->index        = 0;
        $this->eof          = count($this->bencoded);
        $this->decoded      = null;
        $this->valueStack   = [];

        do {
            $this->processChar();
            $this->index += 1;
        } while(!$this->eof());

        if ($this->state !== self::STATE_ROOT) {
            throw new ParseErrorException('Unexpected end of file');
        }

        return $this->decoded;
    }

    private function processChar()
    {
        if ($this->stateContainer()) {
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
                    throw new ParseErrorException('Parser entered invalid state while parsing char');
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
        switch ($this->char()) {
            case 'i':
                $this->pushState(self::STATE_INT);
                $this->pushValue();
                return;

            case 'l':
                $this->pushState(self::STATE_LIST);
                $this->pushValue();
                return;

            case 'd':
                $this->pushState(self::STATE_DICT);
                $this->pushValue();
                return;

            case 'e':
                $this->finalizeContainer();
                return;

            default:

                if ($this->decoded && $this->state === self::STATE_ROOT) {
                    throw new ParseErrorException('Probably some junk after the end of the file');
                }

                $this->pushState(self::STATE_STR);
                $this->pushValue();
                $this->value []= $this->char();
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

            $this->popState();
            $this->popValue($int);
        } else {
            $this->value []= $this->char();
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

            $strChars = [];

            // we have length, just read all string here now

            for ($i = 0; $i < $len; $i++) {
                $this->index += 1;

                if ($this->eof()) {
                    throw new ParseErrorException('Unexpected end of file while processing string');
                }

                $strChars []= $this->char();
            }

            $str = implode($strChars);

            $this->popState();
            $this->popValue($str);
        } else {
            $this->value []= $this->char();
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
                throw new ParseErrorException('Parser entered invalid state while finalizing container');
        }
    }

    private function finalizeList()
    {
        $value = $this->convertArrayToType($this->value, 'listType');

        $this->popState();
        $this->popValue($value);
    }

    private function finalizeDict()
    {
        $dict = [];

        while (count($this->value)) {
            $dictKey = array_shift($this->value);
            if (is_string($dictKey) === false) {
                throw new ParseErrorException('Non string key found in the dictionary');
            }
            if (count($this->value) === 0) {
                throw new ParseErrorException('Dictionary key without corresponding value');
            }
            $dictValue = array_shift($this->value);

            $dict[$dictKey] = $dictValue;
        }

        $value = $this->convertArrayToType($dict, 'dictionaryType');

        $this->popState();
        $this->popValue($value);
    }

    private function pushValue()
    {
        if ($this->state !== self::STATE_ROOT) {
            array_push($this->valueStack, $this->value);
        }
        $this->value = [];
    }

    private function popValue($valueToPrevLevel)
    {
        if ($this->state !== self::STATE_ROOT) {
            $this->value = array_pop($this->valueStack);
            $this->value []= $valueToPrevLevel;
        } else {
            // we have final result
            $this->decoded = $valueToPrevLevel;
        }
    }

    private function pushState(int $newState)
    {
        array_push($this->stateStack, $this->state);
        $this->state = $newState;
    }

    private function popState()
    {
        $this->state = array_pop($this->stateStack);
    }

    private function char()
    {
        return $this->bencoded[$this->index];
    }

    private function eof(): bool
    {
        return $this->index === $this->eof;
    }

    private function convertArrayToType(array $array, $typeOption)
    {
        $type = $this->options[$typeOption];

        if ($type === 'array') {
            return $array;
        }

        if (is_callable($type)) {
            return call_user_func($type, $array);
        }

        if (class_exists($type)) {
            return new $type($array);
        }

        throw new InvalidArgumentException("Invalid type option for '{$typeOption}'. Type should be 'array', class name, or callback");
    }
}
