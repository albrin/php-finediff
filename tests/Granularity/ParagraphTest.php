<?php

namespace FineDiff\Tests\Granularity;

use FineDiff\Delimiters;
use FineDiff\Granularity\GranularityInterface;
use FineDiff\Granularity\Paragraph;
use FineDiff\Parser\Parser;
use PHPUnit\Framework\TestCase;

class ParagraphTest extends TestCase
{
    /**
     * @var GranularityInterface
     */
    protected $granularity;

    /**
     * @var array
     */
    protected $delimiters = [
        Delimiters::PARAGRAPH,
    ];

    public function setUp(): void
    {
        $this->granularity = new Paragraph();
    }

    public function testExtendsAndImplements()
    {
        $this->assertInstanceOf(GranularityInterface::class, $this->granularity);
    }

    public function testGetDelimiters()
    {
        $this->assertEquals($this->granularity->getDelimiters(), $this->delimiters);
    }

    public function testAppendDelimiter()
    {
        $this->granularity[] = Delimiters::WORD;

        $this->assertTrue(isset($this->granularity[1]));
        $this->assertEquals(Delimiters::WORD, $this->granularity[1]);
        $this->assertCount(2, $this->granularity);

        // The appended word delimiter is used for the next diff stage.
        $codes = (new Parser($this->granularity))->parse('same changed', 'same other');

        $this->assertEquals('c5d7i5:other', (string) $codes);
    }
}
