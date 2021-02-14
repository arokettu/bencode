<?php

declare(strict_types=1);

namespace SandFox\Bencode\Tests;

use PHPUnit\Framework\TestCase;
use SandFoxMe\Bencode\Bencode;

class FileTest extends TestCase
{
    public function testFile()
    {
        $file       = '/tmp/bencode_test_dump' . uniqid() . '.torrent';
        $value      = [1, 2, 3, 4, 5];
        $encoded    = Bencode::encode($value);

        Bencode::dump($file, $value);

        $onDisk = file_get_contents($file);
        $this->assertEquals($encoded, $onDisk);

        $loaded = Bencode::load($file);
        $this->assertEquals($loaded, $value);

        unlink($file);
    }

    public function testStream()
    {
        $stream     = fopen('php://temp', 'a+');
        $value      = [1, 2, 3, 4, 5];
        $encoded    = Bencode::encode($value);

        Bencode::encodeToStream($value, $stream);

        rewind($stream);
        $inStream = stream_get_contents($stream);
        $this->assertEquals($encoded, $inStream);

        rewind($stream);
        $loaded = Bencode::decodeStream($stream);
        $this->assertEquals($loaded, $value);
    }
}
