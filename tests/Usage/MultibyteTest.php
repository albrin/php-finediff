<?php

namespace FineDiff\Tests\Usage;

use FineDiff\Diff;
use FineDiff\Granularity\Character;
use FineDiff\Granularity\GranularityInterface;
use FineDiff\Granularity\Paragraph;
use FineDiff\Granularity\Sentence;
use FineDiff\Granularity\Word;
use FineDiff\Render\Html;
use FineDiff\Render\Text;
use PHPUnit\Framework\TestCase;

class MultibyteTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     *
     * @param GranularityInterface $granularity
     * @param string $from
     * @param string $to
     * @param string $operationCodes
     * @param string $html
     */
    public function testMultibyteGranularity(
        GranularityInterface $granularity,
        string $from,
        string $to,
        string $operationCodes,
        string $html
    ) {
        $diff = new Diff($granularity);
        $generated = $diff->getOperationCodes($from, $to);

        $this->assertEquals($operationCodes, (string) $generated);
        $this->assertEquals($to, (new Text())->process($from, $generated));
        $this->assertEquals($html, (new Html())->process($from, $generated));
        $this->assertEquals($html, $diff->render($from, $to));
    }

    /**
     * @return array
     */
    public function dataProvider(): array
    {
        return [
            'character cyrillic' => [
                new Character(),
                'кот',
                'кит',
                'cdi:иc',
                'к<del>о</del><ins>и</ins>т',
            ],
            'character emoji' => [
                new Character(),
                'a😀b',
                'a🎉b',
                'cdi:🎉c',
                'a<del>😀</del><ins>🎉</ins>b',
            ],
            'character emoji insert' => [
                new Character(),
                'test',
                'te🎉st',
                'c2i:🎉c2',
                'te<ins>🎉</ins>st',
            ],
            'word cyrillic' => [
                new Word(),
                'привет мир',
                'привет друг',
                'c7d3i4:друг',
                'привет <del>мир</del><ins>друг</ins>',
            ],
            'word emoji' => [
                new Word(),
                'good 🐘 day',
                'good 🐘 night',
                'c7d3i5:night',
                'good 🐘 <del>day</del><ins>night</ins>',
            ],
            'sentence cyrillic' => [
                new Sentence(),
                'Это тест. Конец.',
                'Это тест. Новое.',
                'c9d7i7: Новое.',
                'Это тест.<del> Конец.</del><ins> Новое.</ins>',
            ],
            'sentence emoji' => [
                new Sentence(),
                'Go 🐘. Stop.',
                'Go 🐘. Wait.',
                'c5d6i6: Wait.',
                'Go 🐘.<del> Stop.</del><ins> Wait.</ins>',
            ],
            'paragraph cyrillic' => [
                new Paragraph(),
                "Первый абзац.\nВторой абзац.",
                "Первый абзац.\nТретий абзац.",
                "c14d13i13:Третий абзац.",
                "Первый абзац.\n<del>Второй абзац.</del><ins>Третий абзац.</ins>",
            ],
            'paragraph emoji' => [
                new Paragraph(),
                "Line 🐘\nSecond.",
                "Line 🐘\nChanged.",
                "c7d7i8:Changed.",
                "Line 🐘\n<del>Second.</del><ins>Changed.</ins>",
            ],
        ];
    }
}
