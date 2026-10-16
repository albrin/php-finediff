<?php

namespace FineDiff\Tests\Parser;

use FineDiff\Exceptions\GranularityCountException;
use FineDiff\Parser\OperationCodesInterface;
use FineDiff\Granularity\Character;
use FineDiff\Granularity\Paragraph;
use FineDiff\Granularity\Word;
use FineDiff\Parser\Parser;
use FineDiff\Parser\ParserInterface;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use stdClass;
use TypeError;

class ParserTest extends TestCase
{
    /**
     * @var ParserInterface
     */
    protected $parser;

    public function setUp(): void
    {
        $granularity  = new Character();
        $this->parser = new Parser($granularity);
    }

    public function tearDown(): void
    {
        m::close();
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(ParserInterface::class, $this->parser);
    }

    public function testDefaultOperationCodes()
    {
        $operation_codes = $this->parser->getOperationCodes();
        $this->assertInstanceOf(OperationCodesInterface::class, $operation_codes);
    }

    public function testSetOperationCodes()
    {
        $operation_codes = m::mock(OperationCodesInterface::class);
        $operation_codes->shouldReceive('foo')->andReturn('bar');
        $this->parser->setOperationCodes($operation_codes);

        $operation_codes = $this->parser->getOperationCodes();
        $this->assertEquals($operation_codes->foo(), 'bar');
    }

    public function testParseBadGranularity()
    {
        $granularity = m::mock(Character::class);
        $granularity->shouldReceive('count')->andReturn(0);
        $parser = new Parser($granularity);

        $this->expectException(GranularityCountException::class);

        $parser->parse('hello world', 'hello2 worl');
    }

    public function testParseSetOperationCodes()
    {
        $operation_codes = m::mock(OperationCodesInterface::class);
        $operation_codes->shouldReceive('setOperationCodes')->once();
        $this->parser->setOperationCodes($operation_codes);

        $this->parser->parse('Hello worlds', 'Hello2 world');

        $this->assertTrue(true);
    }

    public function testSetGranularity()
    {
        $from = 'hello world';
        $to = 'hello there';

        $this->assertEquals('c6d2i3:thecd2i:e', (string) $this->parser->parse($from, $to));

        $word = new Word();
        $this->parser->setGranularity($word);

        $this->assertSame($word, $this->parser->getGranularity());
        $this->assertEquals('c6d5i5:there', (string) $this->parser->parse($from, $to));
    }

    /**
     * @dataProvider nonStringDelimiterProvider
     * @param mixed $delimiter
     */
    public function testNonStringDelimitersAreCoerced($delimiter, string $expected)
    {
        $granularity = new Paragraph();
        $granularity[0] = $delimiter;

        $this->assertEquals($expected, (string) (new Parser($granularity))->parse('ab', 'ac'));
    }

    /**
     * @return array<string, array{0: mixed, 1: string}>
     */
    public function nonStringDelimiterProvider(): array
    {
        return [
            'zero' => [0, 'cdi:c'],
            'float zero' => [0.0, 'cdi:c'],
            'false' => [false, 'cdi:c'],
            'null' => [null, 'cdi:c'],
            'empty array' => [[], 'cdi:c'],
            'fifteen' => [15, 'd2i2:ac'],
            'float' => [1.5, 'd2i2:ac'],
            'true' => [true, 'd2i2:ac'],
        ];
    }

    public function testNonScalarDelimiterIsRejected()
    {
        $granularity = new Paragraph();
        $granularity[0] = new stdClass();

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('strcspn(): Argument #2 ($characters) must be of type string, stdClass given');

        (new Parser($granularity))->parse('ab', 'ac');
    }

    public function testArrayDelimiterIsRejected()
    {
        $granularity = new Paragraph();
        $granularity[0] = ['x'];

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('strcspn(): Argument #2 ($characters) must be of type string, array given');

        (new Parser($granularity))->parse('ab', 'ac');
    }
}
