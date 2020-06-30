<?php

namespace SandFoxMe\Bencode\Exceptions;

class_alias(
    'SandFox\\Bencode\\Exceptions\\InvalidArgumentException',
    'SandFoxMe\\Bencode\\Exceptions\\InvalidArgumentException'
);

if (false) {
    /**
     * @deprecated
     */
    class InvalidArgumentException extends \SandFox\Bencode\Exceptions\InvalidArgumentException
    {
    }
}
