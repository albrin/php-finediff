<?php

namespace FineDiff\Granularity;

use ArrayAccess;
use Countable;

/**
 * @extends ArrayAccess<int|string, mixed>
 */
interface GranularityInterface extends ArrayAccess, Countable
{
    /**
     * Get the delimiters that make up the granularity.
     *
     * @return array<int|string, mixed>
     */
    public function getDelimiters(): array;

    /**
     * Set the delimiters that make up the granularity.
     *
     * @param array<int|string, mixed> $delimiters
     * @return void
     */
    public function setDelimiters(array $delimiters);
}
