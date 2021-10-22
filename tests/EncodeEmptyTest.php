<?php

declare(strict_types=1);

namespace SandFox\Bencode\Tests;

use PHPUnit\Framework\TestCase;
use SandFox\Bencode\Bencode;
use SandFox\Bencode\Exceptions\InvalidArgumentException;
use SandFox\Bencode\Types\BencodeSerializable;

class EncodeEmptyTest extends TestCase
{
    public function testNoRootNull(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to encode an empty value');

        Bencode::encode(null);
    }

    public function testNoRootFalse(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to encode an empty value');

        Bencode::encode(false);
    }

    public function testNoSerializableNull(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to encode an empty value');

        Bencode::encode(new class implements BencodeSerializable {
            public function bencodeSerialize(): mixed
            {
                return null;
            }
        });
    }

    public function testNoSerializableFalse(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to encode an empty value');

        Bencode::encode(new class implements BencodeSerializable {
            public function bencodeSerialize(): bool
            {
                return false;
            }
        });
    }

    public function testSkipInList(): void
    {
        self::assertEquals(
            'li1ei2ei3ei4ei5ee',
            Bencode::encode([1, 2, false, 3, 4, null, 5])
        );
    }

    public function testSkipInDict(): void
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

    public function testSkipSerializableFalseInList(): void
    {
        $emptyValue = new class implements BencodeSerializable {
            public function bencodeSerialize(): bool
            {
                return false;
            }
        };

        self::assertEquals('le', Bencode::encode([$emptyValue]));
    }

    public function testSkipSerializableFalseInDict(): void
    {
        $emptyValue = new class implements BencodeSerializable {
            public function bencodeSerialize(): bool
            {
                return false;
            }
        };

        self::assertEquals('de', Bencode::encode(['empty' => $emptyValue]));
    }
}
