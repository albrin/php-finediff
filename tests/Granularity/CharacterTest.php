<?php

namespace FineDiff\Tests\Granularity;

use ArrayAccess;
use FineDiff\Delimiters;
use FineDiff\Granularity\Character;
use FineDiff\Granularity\GranularityInterface;
use PHPUnit\Framework\TestCase;
use stdClass;
use TypeError;

class CharacterTest extends TestCase
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
        Delimiters::SENTENCE,
        Delimiters::WORD,
        Delimiters::CHARACTER,
    ];

    public function setUp(): void
    {
        $this->granularity = new Character();
    }

    public function testExtendsAndImplements()
    {
        $this->assertInstanceOf(GranularityInterface::class, $this->granularity);
    }

    public function testGetDelimiters()
    {
        $this->assertEquals($this->granularity->getDelimiters(), $this->delimiters);
    }

    public function testSetDelimiters()
    {
        $arr = array('one', 'two');
        $this->granularity->setDelimiters($arr);
        $this->assertEquals($this->granularity->getDelimiters(), $arr);
    }

    public function testCountable()
    {
        $this->assertCount(count($this->granularity), $this->delimiters);
    }

    public function testArrayAccess()
    {
        // Exists
        for ($i = 0; $i < count($this->delimiters) + 1; $i++) {

            if ($i !== count($this->delimiters)) {
                $this->assertTrue(isset($this->granularity[$i]));
            } else {
                $this->assertFalse(isset($this->granularity[$i]));
            }
        }

        // Get
        for ($i = 0; $i < count($this->delimiters) + 1; $i++) {

            if ($i !== count($this->delimiters)) {
                $this->assertEquals($this->granularity[$i], $this->delimiters[$i]);
            } else {
                $this->assertNull($this->granularity[$i]);
            }
        }

        // Set
        for ($i = 0; $i < count($this->delimiters) + 1; $i++) {

            $rand = mt_rand(0, 1000);

            $this->granularity[$i] = $rand;
            $this->assertEquals($this->granularity[$i], $rand);
        }

        $this->assertEquals(count($this->granularity), count($this->delimiters) + 1);

        // Unset
        unset($this->granularity[ count($this->delimiters) ]);
        $this->assertCount(count($this->granularity), $this->delimiters);
    }

    public function testStringOffset()
    {
        $this->granularity['extra'] = Delimiters::CHARACTER;

        $this->assertTrue(isset($this->granularity['extra']));
        $this->assertEquals(Delimiters::CHARACTER, $this->granularity['extra']);

        unset($this->granularity['extra']);
        $this->assertFalse(isset($this->granularity['extra']));
    }

    public function testCoercedOffsetsMatchArrayKeys()
    {
        $this->assertTrue(isset($this->granularity[1.9]));
        $this->assertEquals($this->delimiters[1], $this->granularity[1.9]);
        $this->assertEquals($this->delimiters[0], $this->granularity[false]);
        $this->assertFalse(isset($this->granularity[null]));
        $this->assertNull($this->granularity[null]);

        $this->granularity[true] = 'coerced';
        $this->assertEquals('coerced', $this->granularity[1]);

        unset($this->granularity[null]);
        $this->assertFalse(isset($this->granularity['']));
    }

    public function testIllegalOffset()
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('Cannot access offset of type stdClass on array');

        $offset = new stdClass();
        $this->granularity[$offset] = 'x';
    }
}
