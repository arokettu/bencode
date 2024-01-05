<?php

namespace Arokettu\Bencode\Types;

interface CallbackHandler
{
    /**
     * @return null|false Return false to stop processing
     */
    public function __invoke(array $keys, mixed $value): ?bool;
}
