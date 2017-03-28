<?php

use PHPUnit\Framework\TestCase;
use SandFoxMe\Bencode\Bencode;

class FileTest extends TestCase
{
    public function testFile()
    {
        $file       = '/tmp/bencode_test_dump'. uniqid() .'.torrent';
        $value      = [1, 2, 3, 4, 5];
        $encoded    = Bencode::encode($value);

        Bencode::dump($file, $value);

        $onDisk = file_get_contents($file);

        $this->assertEquals($encoded, $onDisk);

        $loaded = Bencode::load($file);

        $this->assertEquals($loaded, $value);

        unlink($file);
    }
}
