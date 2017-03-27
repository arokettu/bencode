<?php

use PHPUnit\Framework\TestCase;
use SandFoxMe\Bencode\Bencode;
use SandFoxMe\Bencode\Exceptions\ParseErrorException;

/**
 * Class DecodeTest
 *
 * Testing some overall decoding features. Decoding exact types is tested in their own classes
 */
class DecodeTest extends TestCase
{
    public function testJunk()
    {
        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage('Probably some junk after the end of the file');

        Bencode::decode('i0ejunk');
    }
}
