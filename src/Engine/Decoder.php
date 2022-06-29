<?php

declare(strict_types=1);

namespace SandFox\Bencode\Engine;

use SandFox\Bencode\Exceptions\InvalidArgumentException;
use SandFox\Bencode\Exceptions\ParseErrorException;
use SandFox\Bencode\Util\IntUtil;

use function Arokettu\IsResource\try_get_resource_type;

/**
 * @internal
 */
final class Decoder
{
    private mixed $decoded;

    private int $state;
    private \SplStack $stateStack;
    private \SplQueue|null $value;
    private \SplStack $valueStack;

    private const STATE_ROOT = 1;
    private const STATE_LIST = 2;
    private const STATE_DICT = 3;

    /**
     * @param resource $stream
     * @param \Closure $listHandler
     * @param \Closure $dictHandler
     * @param \Closure $bigIntHandler
     */
    public function __construct(
        private $stream,
        private \Closure $listHandler,
        private \Closure $dictHandler,
        private \Closure $bigIntHandler,
    ) {
        if (try_get_resource_type($stream) !== 'stream') {
            throw new InvalidArgumentException('Input is not a valid stream');
        }
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

        if (feof($this->stream) && $c === '') {
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
        // handling numbers longer than 8092 digits is out of the scope of this library
        $result = stream_get_line($this->stream, 8092, $delimiter);

        if ($result === false) {
            return false;
        }

        // validate the delimiter too
        fseek($this->stream, -\strlen($delimiter), SEEK_CUR);
        $d = fread($this->stream, \strlen($delimiter));

        return $d === $delimiter ? $result : false;
    }

    private function processInteger(): void
    {
        $intStr = $this->readInteger('e');

        if ($intStr === false) {
            throw new ParseErrorException("Unexpected end of file while processing integer");
        }

        if (!IntUtil::isValid($intStr)) {
            throw new ParseErrorException("Invalid integer format: '{$intStr}'");
        }

        $int = \intval($intStr);

        $this->finalizeScalar(
            \strval($int) === $intStr ?         // detect overflow
                $int :                          // not overflown: native int
                ($this->bigIntHandler)($intStr) // overflown: handle big int
        );
    }

    private function processString(): void
    {
        // rewind back 1 character because it's a part of string length
        fseek($this->stream, -1, SEEK_CUR);

        $lenStr = $this->readInteger(':');

        if ($lenStr === false) {
            throw new ParseErrorException('Unexpected end of file while processing string');
        }

        $len = \intval($lenStr);

        if (\strval($len) !== $lenStr || $len < 0) {
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
        $this->pop(($this->listHandler)(iterator_to_array($this->value)));
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

        $this->pop(($this->dictHandler)(iterator_to_array($dictBuilder())));
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
}
