<?php

declare(strict_types=1);

namespace SandFox\Bencode\Legacy;

const NS = 'SandFox\\Bencode\\';
const PREFIX = 'Arokettu\\Bencode\\';
const PREFIX_LEN = 17;

spl_autoload_register(function (string $class_name) {
    if (str_starts_with($class_name, PREFIX)) {
        $realName = NS . substr($class_name, PREFIX_LEN);
        class_alias($realName, $class_name);
        return true;
    }

    return null;
});
