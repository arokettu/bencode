<?php

namespace SandFoxMe\Bencode
{
    /**
     * @deprecated \SandFox\Bencode\Bencode
     */
    class Bencode extends \SandFox\Bencode\Bencode {}
}

namespace SandFoxMe\Bencode\Engine
{
    /**
     * @deprecated \SandFox\Bencode\Engine\Decoder
     */
    class Decoder extends \SandFox\Bencode\Engine\Decoder {}

    /**
     * @deprecated \SandFox\Bencode\Engine\Encoder
     */
    class Encoder extends \SandFox\Bencode\Engine\Encoder {}
}

namespace SandFoxMe\Bencode\Types
{
    /**
     * @deprecated \SandFox\Bencode\Types\BencodeSerializable
     */
    interface BencodeSerializable extends \SandFox\Bencode\Types\BencodeSerializable {}

    /**
     * @deprecated \SandFox\Bencode\Types\ListType
     */
    class ListType extends \SandFox\Bencode\Types\ListType {}
}

namespace {
    // to catch exceptions properly we need to create aliases
    // hopefully no one uses our exceptions without base classes
    // so this file is always loaded

    class_alias(
        'SandFox\\Bencode\\Exceptions\\BencodeException',
        'SandFoxMe\\Bencode\\Exceptions\\BencodeException'
    );
    class_alias(
        'SandFox\\Bencode\\Exceptions\\InvalidArgumentException',
        'SandFoxMe\\Bencode\\Exceptions\\InvalidArgumentException'
    );
    class_alias(
        'SandFox\\Bencode\\Exceptions\\ParseErrorException',
        'SandFoxMe\\Bencode\\Exceptions\\ParseErrorException'
    );
    class_alias(
        'SandFox\\Bencode\\Exceptions\\RuntimeException',
        'SandFoxMe\\Bencode\\Exceptions\\RuntimeException'
    );
}
