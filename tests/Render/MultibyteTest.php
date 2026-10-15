<?php

namespace FineDiff\Tests\Render;

use FineDiff\Render\Html;
use FineDiff\Render\Text;
use PHPUnit\Framework\TestCase;

class MultibyteTest extends TestCase
{
    /**
     * Inserted UTF-8 text is stored in the opcode string, and the cursor is a
     * character offset. Reading that cursor as a byte drops every opcode after
     * a Cyrillic character or an emoji.
     *
     * @dataProvider operationCodeProvider
     *
     * @param string $from
     * @param string $operationCodes
     * @param string $text
     * @param string $html
     */
    public function testProcess($from, $operationCodes, $text, $html)
    {
        $this->assertEquals($text, (new Text())->process($from, $operationCodes));
        $this->assertEquals($html, (new Html())->process($from, $operationCodes));
    }

    /**
     * @return array
     */
    public function operationCodeProvider(): array
    {
        return [
            'cyrillic insert between copies' => [
                'abcd',
                'ci:яdc2',
                'aяcd',
                'a<ins>я</ins><del>b</del>cd',
            ],
            'cyrillic multi-character insert' => [
                'abc',
                'c1i2:яяc2',
                'aяяbc',
                'a<ins>яя</ins>bc',
            ],
            'emoji insert between copies' => [
                'abcd',
                'ci:🎉dc2',
                'a🎉cd',
                'a<ins>🎉</ins><del>b</del>cd',
            ],
            'emoji with explicit length' => [
                'Hello worlds',
                'c6i2:🐘 c6',
                'Hello 🐘 worlds',
                'Hello <ins>🐘 </ins>worlds',
            ],
        ];
    }
}
