<?php

namespace FineDiff;

abstract class Delimiters
{
    const PARAGRAPH = "\n\r";
    const SENTENCE = ".\n\r";
    const WORD = " \t.\n\r";
    const CHARACTER = '';

    /**
     * Do not allow this class to be instantiated.
     *
     * Constants-only class: the constructor is private and has no behaviour,
     * so there is nothing meaningful to execute.
     *
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }
}
