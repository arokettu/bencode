<?php

declare(strict_types=1);

namespace Arokettu\Bencode\Engine;

use Arokettu\Bencode\Exceptions\InvalidArgumentException;
use Arokettu\Bencode\Exceptions\ParseErrorException;
use Arokettu\Bencode\Util\IntUtil;
use Closure;
use Ds\Stack;

use function Arokettu\IsResource\try_get_resource_type;

/**
 * @internal
 */
final class CallbackReader
{
    private bool $decoded;

    private int $state;
    private Stack $stateStack;
    private Stack $keyStack;

    private const STATE_ROOT = 1;
    private const STATE_LIST = 2;
    private const STATE_DICT = 3;
    private const STATE_DICT_KEY = 4;

    /**
     * @param resource $stream
     */
    public function __construct(
        private $stream,
        private readonly Closure $callback,
        private readonly Closure $bigIntHandler,
    ) {
        if (try_get_resource_type($stream) !== 'stream') {
            throw new InvalidArgumentException('Input is not a valid stream');
        }
    }

    public function read(): void
    {
        $this->state        = self::STATE_ROOT;
        $this->stateStack   = new Stack();
        $this->decoded      = false;
        $this->keyStack     = new Stack();

        while (!feof($this->stream)) {
            $this->processChar();
        }

        /** @psalm-suppress TypeDoesNotContainType too smart! */
        if ($this->state !== self::STATE_ROOT || !$this->decoded) {
            throw new ParseErrorException('Unexpected end of file');
        }
    }

    private function processChar(): void
    {
        $c = fread($this->stream, 1);

        if (feof($this->stream) && $c === '') {
            return;
        }

        if ($this->decoded && $this->state === self::STATE_ROOT) {
            throw new ParseErrorException('Probably some junk after the end of the file');
        }

        if ($this->state === self::STATE_DICT_KEY) {
            // next value can only be finalizer or null
            match ($c) {
                'e' => $this->finalizeContainer(),
                default => $this->processString(),
            };
            return;
        }

        match ($c) {
            'i' => $this->processInteger(),
            'l' => $this->push(self::STATE_LIST),
            'd' => $this->push(self::STATE_DICT_KEY),
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

        if ($this->state === self::STATE_DICT_KEY) {
            $prevKey = $this->keyStack->pop();
            if ($prevKey && strcmp($prevKey, $str) >= 0) {
                throw new ParseErrorException("Invalid order of dictionary keys: '{$str}' after '{$prevKey}'");
            }
            $this->keyStack->push($str);
            $this->state = self::STATE_DICT;
        } else {
            $this->finalizeScalar($str);
        }
    }

    private function finalizeContainer(): void
    {
        $this->pop();
    }

    /**
     * Send parsed value to the current container
     * @param mixed $value
     */
    private function finalizeScalar(mixed $value): void
    {
        switch ($this->state) {
            case self::STATE_ROOT:
                break;
            case self::STATE_LIST:
                $index = $this->keyStack->pop();
                $index += 1;
                $this->keyStack->push($index);
                break;
            case self::STATE_DICT:
                $this->state = self::STATE_DICT_KEY;
                break;
            default:
                throw new \LogicException();
        }

        ($this->callback)(array_reverse($this->keyStack->toArray()), $value);
    }

    /**
     * Push previous layer to the stack and set new state
     * @param int $newState
     */
    private function push(int $newState): void
    {
        $this->stateStack->push($this->state);
        $this->state = $newState;

        $this->keyStack->push(match ($newState) {
            self::STATE_LIST => -1,
            self::STATE_DICT_KEY => null,
        });
    }

    /**
     * Pop previous layer from the stack and give it a parsed value
     */
    private function pop(): void
    {
        $this->state = $this->stateStack->pop();
        $this->keyStack->pop();

        if ($this->state === self::STATE_ROOT) {
            $this->decoded = true;
        }
        if ($this->state === self::STATE_DICT) {
            $this->state = self::STATE_DICT_KEY;
        }
    }
}
