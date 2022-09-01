<?php

declare(strict_types=1);

namespace SandFox\Bencode\Tests;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use SandFox\Bencode\Bencode;
use SandFox\Bencode\Exceptions\FileNotReadableException;
use SandFox\Bencode\Exceptions\FileNotWritableException;
use SandFox\Bencode\Exceptions\InvalidArgumentException;

class FileTest extends TestCase
{
    public function testFile(): void
    {
        $file       = '/tmp/bencode_test_dump' . uniqid() . '.torrent';
        $value      = [1, 2, 3, 4, 5];
        $encoded    = Bencode::encode($value);

        Bencode::dump($value, $file);

        $onDisk = file_get_contents($file);
        self::assertEquals($encoded, $onDisk);

        $loaded = Bencode::load($file);
        self::assertEquals($loaded, $value);

        Bencode::dump($value, $file); // dump again to the existing file to check permissions

        unlink($file);
    }

    public function testStream(): void
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

    public function testDefaultStream(): void
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

    public function testInvalidFileRead(): void
    {
        $this->expectException(FileNotReadableException::class);
        vfsStream::setup();
        $stream = vfsStream::newFile('test', 0000);

        self::assertEquals(false, Bencode::load($stream->url()));
    }

    public function testInvalidFileWrite(): void
    {
        $this->expectException(FileNotWritableException::class);
        vfsStream::setup();
        $stream = vfsStream::newFile('test', 0000);

        self::assertEquals(false, Bencode::dump([], $stream->url()));
    }

    public function testEncodeToInvalidResource(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Output is not a valid stream');

        Bencode::encodeToStream([], false);
    }

    public function testDecodeFromInvalidResource(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Input is not a valid stream');

        Bencode::decodeStream(false);
    }
}
