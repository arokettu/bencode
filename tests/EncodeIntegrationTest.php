<?php

declare(strict_types=1);

namespace SandFox\Bencode\Tests;

use PHPUnit\Framework\TestCase;
use SandFox\Bencode\Bencode;

class EncodeIntegrationTest extends TestCase
{
    public function testAllTypes(): void
    {
        // just so some data in combinations
        $data1 = [
            'integer'   => 1,           // 7:integeri1e
            'list'      => [
                1, 2, 3, 'test',
                ['list', 'in', 'list'], // l4:list2:in4:liste
                ['dict' => 'in list'],  // d4:dict7:in liste
            ],                          // 4:listli1ei2ei3e4:testl4:list2:in4:listed4:dict7:in listee
            'dict'      => [
                'int' => 123, 'list' => []
            ],                          // 4:dictd3:inti123e4:listlee
            'string'    => 'str',       // 6:string3:str
        ];
        $data2 = [
            'integer'   => 1,
            'string'    => 'str',
            'dict'      => ['list' => [], 'int' => 123],
            'list'      => [1, 2, 3, 'test', ['list', 'in', 'list'], ['dict' => 'in list']],
        ];

        $expected = 'd' .
            '4:dictd3:inti123e4:listlee' .
            '7:integeri1e' .
            '4:listli1ei2ei3e4:testl4:list2:in4:listed4:dict7:in listee' .
            '6:string3:str' .
            'e';

        $result1 = Bencode::encode($data1);
        $result2 = Bencode::encode($data2);

        self::assertEquals($expected, $result1);
        self::assertEquals($result1, $result2); // different order of dict keys should not change the result
    }
}
