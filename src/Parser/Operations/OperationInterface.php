<?php

namespace FineDiff\Parser\Operations;

interface OperationInterface
{
    /**
     * Copy code
     *
     * @var string
     */
    const COPY = 'c';

    /**
     * Delete code
     *
     * @var string
     */
    const DELETE = 'd';

    /**
     * Insert code
     *
     * @var string
     */
    const INSERT = 'i';

    /**
     * Characters consumed from the original text.
     *
     * Parser-built operations return an int. Replace also accepts a string
     * from-length in its constructor and returns that value unchanged.
     *
     * @return int|string
     */
    public function getFromLen();

    /**
     * @return int
     */
    public function getToLen();

    /**
     * @return string Operation code for this operation.
     */
    public function getOperationCode(): string;
}
