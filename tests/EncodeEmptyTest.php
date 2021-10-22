<?php

declare(strict_types=1);

namespace SandFox\Bencode\Tests;

use PHPUnit\Framework\TestCase;
use SandFoxMe\Bencode\Bencode;
use SandFoxMe\Bencode\Exceptions\InvalidArgumentException;
use SandFoxMe\Bencode\Types\BencodeSerializable;

class EncodeEmptyTest extends TestCase
{
    public function testNoRootNull()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to encode an empty value');

        Bencode::encode(null);
    }

    public function testNoRootFalse()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to encode an empty value');

        Bencode::encode(false);
    }

    public function testNoSerializableNull()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to encode an empty value');

        Bencode::encode(new class implements BencodeSerializable {
            public function bencodeSerialize()
            {
                return null;
            }
        });
    }

    public function testNoSerializableFalse()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to encode an empty value');

        Bencode::encode(new class implements BencodeSerializable {
            public function bencodeSerialize()
            {
                return false;
            }
        });
    }

    public function testSkipInList()
    {
        self::assertEquals(
            'li1ei2ei3ei4ei5ee',
            Bencode::encode([1, 2, false, 3, 4, null, 5])
        );
    }

    public function testSkipInDict()
    {
        self::assertEquals(
            'd1:ai1e1:ci2e1:ei3ee',
            Bencode::encode([
                'a' => 1,
                'b' => null,
                'c' => 2,
                'd' => false,
                'e' => 3,
            ])
        );
    }

    public function testSkipSerializableFalseInList()
    {
        $emptyValue = new class implements BencodeSerializable {
            public function bencodeSerialize()
            {
                return false;
            }
        };

        self::assertEquals('le', Bencode::encode([$emptyValue]));
    }

    public function testSkipSerializableFalseInDict()
    {
        $emptyValue = new class implements BencodeSerializable {
            public function bencodeSerialize()
            {
                return false;
            }
        };

        self::assertEquals('de', Bencode::encode(['empty' => $emptyValue]));
    }
}
