<?php

declare(strict_types=1);

namespace Arokettu\Bencode\Exceptions;

use UnexpectedValueException;

final class ValueNotSerializableException extends UnexpectedValueException implements BencodeException
{
}
