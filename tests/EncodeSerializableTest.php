<?php

declare(strict_types=1);

namespace SandFox\Bencode\Tests;

use PHPUnit\Framework\TestCase;
use SandFox\Bencode\Bencode;
use SandFox\Bencode\Types\BencodeSerializable;

class EncodeSerializableTest extends TestCase
{
    public function testSerializable(): void
    {
        // test returning scalar
        $dataScalar = new class implements BencodeSerializable {
            public function bencodeSerialize(): mixed
            {
                return 'Test';
            }
        };

        // test returning object which is also serializable
        $dataRecursion = new class ($dataScalar) implements BencodeSerializable {
            public function __construct(
                private BencodeSerializable $data,
            ) {}

            public function bencodeSerialize(): mixed
            {
                return $this->data;
            }
        };

        // Test returning array
        $dataArray = new class implements BencodeSerializable {
            public function bencodeSerialize(): mixed
            {
                return [
                    1,
                    2,
                    3,
                ];
            }
        };

        self::assertEquals('4:Test', Bencode::encode($dataScalar));
        self::assertEquals('4:Test', Bencode::encode($dataRecursion));
        self::assertEquals('li1ei2ei3ee', Bencode::encode($dataArray));
    }

    public function testJsonSerializable(): void
    {
        // test returning scalar
        $dataScalar = new class implements \JsonSerializable {
            public function jsonSerialize(): mixed
            {
                return 'Test';
            }
        };

        // test returning object which is also serializable
        $dataRecursion = new class ($dataScalar) implements \JsonSerializable {
            public function __construct(
                private \JsonSerializable $data,
            ) {}

            public function jsonSerialize(): mixed
            {
                return $this->data;
            }
        };

        // Test returning array
        $dataArray = new class implements \JsonSerializable {
            public function jsonSerialize(): mixed
            {
                return [
                    1,
                    2,
                    3,
                ];
            }
        };

        self::assertEquals('4:Test', Bencode::encode($dataScalar, useJsonSerializable: true));
        self::assertEquals('li1ei2ei3ee', Bencode::encode($dataArray, useJsonSerializable: true));
    }
}
