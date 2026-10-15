<?php

namespace FineDiff\Tests\Usage;

use FineDiff\Diff;
use FineDiff\Granularity\Word;
use FineDiff\Render\Html;
use FineDiff\Render\Text;
use PHPUnit\Framework\TestCase;

/**
 * Regression for EcoOnline/php-finediff#9.
 *
 * Inserting a paragraph at Word granularity must be a single insert.
 * Byte offsets from strcspn/strspn used to be treated as character offsets,
 * so a preceding U+2019 apostrophe shifted the word boundary and the new
 * paragraph was spliced into the middle of "discretion".
 */
class WordParagraphInsertTest extends TestCase
{
    public function testMinimalCurlyApostropheDoesNotSplitTheFollowingWord()
    {
        $from = "It\u{2019}s discretion.\n\nThe rest stays.";
        $to = "It\u{2019}s discretion.\n\n# Title\n\nThe rest stays.";

        $diff = new Diff(new Word());
        $operationCodes = $diff->getOperationCodes($from, $to);

        $this->assertEquals("c18i9:# Title\n\nc15", (string) $operationCodes);
        $this->assertEquals($to, (new Text())->process($from, $operationCodes));
        $this->assertEquals(
            "It&rsquo;s discretion.\n\n<ins># Title\n\n</ins>The rest stays.",
            (new Html())->process($from, $operationCodes)
        );
        $this->assertEquals(
            "It&rsquo;s discretion.\n\n<ins># Title\n\n</ins>The rest stays.",
            $diff->render($from, $to)
        );
    }

    /**
     * Before/after text reconstructed from the samples in
     * EcoOnline/php-finediff#9. Apostrophes are U+2019, as produced by
     * the HTML renderer (&rsquo;).
     */
    public function testIssue9InsertsTheHeadingParagraphCleanly()
    {
        $first = <<<'TXT'
There’s a way to get your character to be functionally bullet-proof in Cyberpunk 2077, but it’s not quite a build (you don’t need to invest any attribute points or perk points to do this) and it’s not really an exploit, either (you’re just using in-game items the way they were designed to be used). Still, it’s incredibly broken and trivializes most of the game, so use it at your own discretion.
TXT;
        // The issue sample keeps a space between the period and the blank line.
        $first .= ' ';
        $second = <<<'TXT'
The key to this is getting your hands on an Armadillo clothing mod, which doesn’t have a static drop location, but there’s a fair chance you’ll find one while completing [NCPD Hustles](/cyberpunk-2077/guide/ncpd-scanner-hustles/introduction/overview), [Gigs](/cyberpunk-2077/guide/gigs/introduction/overview) or [Side Jobs](/cyberpunk-2077/guide/side-jobs/introduction/overview), so just keep an eye out and loot diligently as you clear map markers.
TXT;
        $heading = "# Cyberpunk Armadillo Clothing Mod\n\n";
        $from = $first."\n\n".$second;
        $to = $first."\n\n".$heading.$second;

        $diff = new Diff(new Word());
        $operationCodes = $diff->getOperationCodes($from, $to);
        $html = $diff->render($from, $to);

        $this->assertEquals('c400i36:'.$heading.'c449', (string) $operationCodes);
        $this->assertEquals($to, (new Text())->process($from, $operationCodes));
        $this->assertEquals($this->cleanInsert($first, $heading, $second), $html);
        $this->assertStringNotContainsString('<del>', $html);
        $this->assertStringNotContainsString('di<del>', $html);
    }

    /**
     * HTML for an unchanged prefix, one inserted block, and an unchanged suffix.
     *
     * @param string $prefix
     * @param string $inserted
     * @param string $suffix
     *
     * @return string
     */
    private function cleanInsert($prefix, $inserted, $suffix)
    {
        return $this->encode($prefix."\n\n")
            .'<ins>'.$this->encode($inserted).'</ins>'
            .$this->encode($suffix);
    }

    /**
     * @param string $string
     *
     * @return string
     */
    private function encode($string)
    {
        return htmlentities($string, ENT_COMPAT | (defined('ENT_HTML401') ? ENT_HTML401 : 0), 'UTF-8');
    }
}
