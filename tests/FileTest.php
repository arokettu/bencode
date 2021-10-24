<?php

declare(strict_types=1);

namespace SandFox\Bencode\Tests;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use SandFox\Bencode\Exceptions\FileNotReadableException;
use SandFox\Bencode\Exceptions\FileNotWritableException;
use SandFoxMe\Bencode\Bencode;
use SandFoxMe\Bencode\Exceptions\InvalidArgumentException;

class FileTest extends TestCase
{
    public function testFile()
    {
        $file       = '/tmp/bencode_test_dump' . uniqid() . '.torrent';
        $value      = [1, 2, 3, 4, 5];
        $encoded    = Bencode::encode($value);

        Bencode::dump($file, $value);

        $onDisk = file_get_contents($file);
        self::assertEquals($encoded, $onDisk);

        $loaded = Bencode::load($file);
        self::assertEquals($loaded, $value);

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
        self::assertEquals($encoded, $inStream);

        rewind($stream);
        $loaded = Bencode::decodeStream($stream);
        self::assertEquals($loaded, $value);
    }

    public function testDefaultStream()
    {
        $value      = [1, 2, 3, 4, 5];
        $encoded    = Bencode::encode($value);

        $stream = Bencode::encodeToStream($value);

        rewind($stream);
        $inStream = stream_get_contents($stream);
        self::assertEquals($encoded, $inStream);

        rewind($stream);
        $loaded = Bencode::decodeStream($stream);
        self::assertEquals($loaded, $value);
    }

    public function testInvalidFileRead()
    {
        $this->expectException(FileNotReadableException::class);
        vfsStream::setup();
        $stream = vfsStream::newFile('test', 0000);

        self::assertEquals(false, Bencode::load($stream->url()));
    }

    public function testInvalidFileWrite()
    {
        $this->expectException(FileNotWritableException::class);
        vfsStream::setup();
        $stream = vfsStream::newFile('test', 0000);

        self::assertEquals(false, Bencode::dump($stream->url(), []));
    }

    public function testEncodeToInvalidResource()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Output is not a valid stream');

        Bencode::encodeToStream([], false);
    }

    public function testDecodeFromInvalidResource()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Input is not a valid stream');

        Bencode::decodeStream(false);
    }
}
