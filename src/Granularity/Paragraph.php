<?php

namespace FineDiff\Granularity;

use FineDiff\Delimiters;

class Paragraph implements GranularityInterface
{
    use Granularity;

    /**
     * @var array<int|string, mixed>
     */
    private $delimiters = [
        Delimiters::PARAGRAPH,
    ];
}
