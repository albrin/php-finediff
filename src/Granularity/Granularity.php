<?php

namespace FineDiff\Granularity;

trait Granularity
{
    /**
     * @inheritdoc
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->delimiters[$this->delimiterKey($offset)]);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->delimiters[$this->delimiterKey($offset)] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        // A null offset appends, matching ArrayAccess. It is not key "".
        if ($offset === null) {
            $this->delimiters[] = $value;

            return;
        }

        $this->delimiters[$this->delimiterKey($offset)] = $value;
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->delimiters[$this->delimiterKey($offset)]);
    }

    /**
     * Return the number of delimiters this granularity contains.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->delimiters);
    }

    /**
     * @return array<int|string, mixed>
     */
    public function getDelimiters(): array
    {
        return $this->delimiters;
    }

    /**
     * @param array<int|string, mixed> $delimiters
     * @return void
     */
    public function setDelimiters(array $delimiters)
    {
        $this->delimiters = $delimiters;
    }

    /**
     * Normalize an ArrayAccess offset to a legal PHP array key.
     *
     * Floats and bools are cast to int, and null becomes "", matching PHP
     * array-key coercion. Objects, arrays, and resources are illegal keys.
     *
     * @throws \TypeError
     */
    private function delimiterKey(mixed $offset): int|string
    {
        if (is_int($offset) || is_string($offset)) {
            return $offset;
        }

        if (is_float($offset) || is_bool($offset)) {
            return (int) $offset;
        }

        if ($offset === null) {
            return '';
        }

        throw new \TypeError('Cannot access offset of type '.get_debug_type($offset).' on array');
    }
}
