<?php

namespace FineDiff\Tests\Parser;

use FineDiff\Granularity\Word;
use FineDiff\Parser\Parser;
use FineDiff\Render\Html;
use FineDiff\Render\Text;
use PHPUnit\Framework\TestCase;

class FragmentDiffTest extends TestCase
{
    /**
     * @var Parser
     */
    protected $parser;

    public function setUp(): void
    {
        $this->parser = new Parser(new Word());
    }

    public function testInsertWhenSourceIsEmpty()
    {
        $this->assertDiff('', 'hello world', 'i11:hello world', '<ins>hello world</ins>');
    }

    public function testDeleteWhenTargetIsEmpty()
    {
        $this->assertDiff('hello world', '', 'd11', '<del>hello world</del>');
    }

    public function testInsertTrailingWord()
    {
        // The shared "hello world " is one copy. Only the extra word is inserted.
        $this->assertDiff(
            'hello world ',
            'hello world extra',
            'c12i5:extra',
            'hello world <ins>extra</ins>'
        );
    }

    public function testDeleteTrailingWord()
    {
        // The shared prefix is copied until the target runs out. The leftover word is deleted.
        $this->assertDiff(
            'hello world extra',
            'hello world ',
            'c12d5',
            'hello world <del>extra</del>'
        );
    }

    public function testRepeatedWordAlsoOccursBeforeTheSegment()
    {
        // "repeat" occurs in the shared prefix and again after UNIQUE.
        // The later edit has to use the second "repeat", not the earlier one.
        $this->assertDiff(
            'repeat AAA UNIQUE BBB repeat CCC',
            'repeat XXX UNIQUE YYY repeat ZZZ',
            'c7d4i4:XXX c7d4i4:YYY c7d3i3:ZZZ',
            'repeat <del>AAA </del><ins>XXX </ins>UNIQUE <del>BBB </del><ins>YYY </ins>repeat <del>CCC</del><ins>ZZZ</ins>'
        );
    }

    public function testRepeatedWordAlsoOccursAfterTheSegment()
    {
        // "red" occurs before UNIQUE and again after it. The first replacement
        // must not be paired with the later "red".
        $this->assertDiff(
            'red left UNIQUE mid red right',
            'red LEFT UNIQUE mid red RIGHT',
            'c4d5i5:LEFT c15d5i5:RIGHT',
            'red <del>left </del><ins>LEFT </ins>UNIQUE mid red <del>right</del><ins>RIGHT</ins>'
        );
    }

    public function testRepeatedWordInsidePartialSegment()
    {
        // "go" is repeated in the part before the shared MID anchor.
        $this->assertDiff(
            'go go MID stop',
            'to to MID halt',
            'd6i6:to to c4d4i4:halt',
            '<del>go go </del><ins>to to </ins>MID <del>stop</del><ins>halt</ins>'
        );
    }

    /**
     * @param string $from
     * @param string $to
     * @param string $operationCodes
     * @param string $html
     */
    private function assertDiff($from, $to, $operationCodes, $html)
    {
        $generated = $this->parser->parse($from, $to);

        $this->assertEquals($operationCodes, (string) $generated);
        $this->assertEquals($to, (new Text())->process($from, $generated));
        $this->assertEquals($html, (new Html())->process($from, $generated));
    }
}
