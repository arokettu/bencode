<?php

declare(strict_types=1);

namespace Arokettu\Bencode\Exceptions;

use UnexpectedValueException;

class ParseErrorException extends UnexpectedValueException implements BencodeException
{
}
