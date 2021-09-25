<?php

declare(strict_types=1);

namespace SandFox\Bencode\Engine;

use Brick\Math\BigInteger;
use SandFox\Bencode\Bencode\BigInt;
use SandFox\Bencode\Bencode\Collection;
use SandFox\Bencode\Exceptions\InvalidArgumentException;
use SandFox\Bencode\Exceptions\ParseErrorException;
use SandFox\Bencode\Types\BigIntType;
use SandFox\Bencode\Util\IntUtil;

use function Arokettu\IsResource\try_get_resource_type;

/**
 * Class Decoder
 * @package SandFox\Bencode\Engine
 * @author Anton Smirnov
 * @license MIT
 * @internal
 */
final class Decoder
{
    private mixed $decoded;

    private array $options;

    private int $state;
    private \SplStack $stateStack;
    private \SplQueue|null $value;
    private \SplStack $valueStack;

    private const STATE_ROOT = 1;
    private const STATE_LIST = 2;
    private const STATE_DICT = 3;

    /**
     * @param resource $stream
     * @param string|callable $listType
     * @param string|callable $dictType
     * @param string|callable $bigInt
     */
    public function __construct(
        private $stream,
        string|callable $listType = Collection::ARRAY,
        string|callable $dictType = Collection::ARRAY,
        string|callable $bigInt = BigInt::NONE,
    ) {
        if (try_get_resource_type($stream) !== 'stream') {
            throw new InvalidArgumentException('Input is not a valid stream');
        }

        $this->options = compact('listType', 'dictType', 'bigInt');
    }

    public function decode(): mixed
    {
        $this->state        = self::STATE_ROOT;
        $this->stateStack   = new \SplStack();
        $this->decoded      = null;
        $this->value        = null;
        $this->valueStack   = new \SplStack();

        while (!feof($this->stream)) {
            $this->processChar();
        }

        if ($this->state !== self::STATE_ROOT || $this->decoded === null) {
            throw new ParseErrorException('Unexpected end of file');
        }

        return $this->decoded;
    }

    private function processChar(): void
    {
        $c = fread($this->stream, 1);

        if (feof($this->stream) || $c === '') {
            return;
        }

        if ($this->decoded !== null && $this->state === self::STATE_ROOT) {
            throw new ParseErrorException('Probably some junk after the end of the file');
        }

        match ($c) {
            'i' => $this->processInteger(),
            'l' => $this->push(self::STATE_LIST),
            'd' => $this->push(self::STATE_DICT),
            'e' => $this->finalizeContainer(),
            default => $this->processString(),
        };
    }

    private function readInteger(string $delimiter): string|false
    {
        $pos = ftell($this->stream);
        $readLength = 32; // More than enough for 64 bit int (19 digits + minus + delimiter)

        do {
            fseek($this->stream, $pos, SEEK_SET);
            $int = fread($this->stream, $readLength);

            $position = strpos($int, $delimiter);

            if ($position !== false) {
                $int = substr($int, 0, $position);
                fseek($this->stream, $pos + $position + 1, SEEK_SET);

                return $int;
            }

            fread($this->stream, 1); // trigger feof
            $readLength *= 32; // grow exponentially
        } while (!feof($this->stream) && $readLength < PHP_INT_MAX);

        return false;
    }

    private function processInteger(): void
    {
        $intStr = $this->readInteger('e');

        if ($intStr === false) {
            throw new ParseErrorException("Unexpected end of file while processing integer");
        }

        if (!IntUtil::isValid($intStr)) {
            throw new ParseErrorException("Invalid integer format or integer overflow: '{$intStr}'");
        }

        $int = (int)$intStr;

        // detect overflow
        if ((string)$int === $intStr) {
            $this->finalizeScalar($int);
            return;
        }

        if ($this->options['bigInt'] !== BigInt::NONE) {
            $this->finalizeScalar($this->stringToBigInt($intStr));
            return;
        }

        throw new ParseErrorException("Invalid integer format or integer overflow: '{$intStr}'");
    }

    private function stringToBigInt(string $intStr): mixed
    {
        $bigInt = $this->options['bigInt'];

        return match (true) {
            $bigInt === BigInt::INTERNAL
                => new BigIntType($intStr),
            $bigInt === BigInt::GMP
                => \gmp_init($intStr),
            $bigInt === BigInt::BRICK_MATH
                => BigInteger::of($intStr),
            $bigInt === BigInt::PEAR
                => new \Math_BigInteger($intStr),
            is_callable($bigInt)
                => $bigInt($intStr),
            class_exists($bigInt)
                => new $bigInt($intStr),
            default
                => throw new ParseErrorException('Invalid BigMath mode'),
        };
    }

    private function processString(): void
    {
        // rewind back 1 character because it's a part of string length
        fseek($this->stream, -1, SEEK_CUR);

        $lenStr = $this->readInteger(':');

        if ($lenStr === false) {
            throw new ParseErrorException('Unexpected end of file while processing string');
        }

        $len = (int)$lenStr;

        if ((string)$len !== $lenStr || $len < 0) {
            throw new ParseErrorException("Invalid string length value: '{$lenStr}'");
        }

        // we have length, just read all string here now

        $str = $len === 0 ? '' : fread($this->stream, $len);

        if (\strlen($str) !== $len) {
            throw new ParseErrorException('Unexpected end of file while processing string');
        }

        $this->finalizeScalar($str);
    }

    private function finalizeContainer(): void
    {
        match ($this->state) {
            self::STATE_LIST => $this->finalizeList(),
            self::STATE_DICT => $this->finalizeDict(),
            // @codeCoverageIgnoreStart
            // This exception means that we have a bug in our own code
            default => throw new ParseErrorException('Parser entered invalid state while finalizing container'),
            // @codeCoverageIgnoreEnd
        };
    }

    private function finalizeList(): void
    {
        $value = $this->convertArrayToType(iterator_to_array($this->value), 'listType');

        $this->pop($value);
    }

    private function finalizeDict(): void
    {
        $dictBuilder = function (): \Generator {
            $prevKey = null;

            // we have a queue [key1, value1, key2, value2, key3, value3, ...]
            while (\count($this->value)) {
                $dictKey = $this->value->dequeue();
                if (\is_string($dictKey) === false) {
                    throw new ParseErrorException('Non string key found in the dictionary');
                }
                if (\count($this->value) === 0) {
                    throw new ParseErrorException("Dictionary key without corresponding value: '{$dictKey}'");
                }
                if ($prevKey && strcmp($prevKey, $dictKey) >= 0) {
                    throw new ParseErrorException("Invalid order of dictionary keys: '{$dictKey}' after '{$prevKey}'");
                }
                $dictValue = $this->value->dequeue();

                yield $dictKey => $dictValue;
                $prevKey = $dictKey;
            }
        };

        $value = $this->convertArrayToType(iterator_to_array($dictBuilder()), 'dictType');

        $this->pop($value);
    }

    /**
     * Send parsed value to the current container
     * @param mixed $value
     */
    private function finalizeScalar(mixed $value): void
    {
        if ($this->state !== self::STATE_ROOT) {
            $this->value->enqueue($value);
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
        $this->stateStack->push($this->state);
        $this->state = $newState;

        $this->valueStack->push($this->value);
        $this->value = new \SplQueue();
    }

    /**
     * Pop previous layer from the stack and give it a parsed value
     * @param mixed $valueToPrevLevel
     */
    private function pop(mixed $valueToPrevLevel): void
    {
        $this->state = $this->stateStack->pop();

        if ($this->state !== self::STATE_ROOT) {
            $this->value = $this->valueStack->pop();
            $this->value->enqueue($valueToPrevLevel);
        } else {
            // we have final result
            $this->decoded = $valueToPrevLevel;
        }
    }

    private function convertArrayToType(array $array, string $typeOption): mixed
    {
        $type = $this->options[$typeOption];

        if ($type === Collection::ARRAY) {
            return $array;
        }

        if ($type === Collection::OBJECT) {
            return (object)$array;
        }

        if (is_callable($type)) {
            return \call_user_func($type, $array);
        }

        if (class_exists($type)) {
            return new $type($array);
        }

        throw new InvalidArgumentException(
            "Invalid type option for '{$typeOption}'. Type should be 'array', 'object', class name, or callback"
        );
    }
}
