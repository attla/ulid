<?php

namespace Attla\Ulid;

use Attla\Ulid\Exception\InvalidUlidStringException;

class Ulid extends Factory
{
    /**
     * The time part of the ULID
     *
     * @var string
     */
    private $time;

    /**
     * The random part of the ULID
     *
     * @var string
     */
    private $randomness;

    /**
     * If the ULID is lowercase
     *
     * @var bool
     */
    private $lowercase;

    public function __construct(string $time, string $randomness, bool $lowercase = false)
    {
        $this->time = $time;
        $this->randomness = $randomness;
        $this->lowercase = $lowercase;
    }

    /**
     * Get the time part of the ULID
     *
     * @return string
     */
    public function getTime(): string
    {
        return $this->time;
    }

    /**
     * Get the random part of the ULID
     *
     * @return string
     */
    public function getRandomness(): string
    {
        return $this->randomness;
    }

    /**
     * Get the ULID lowercase flag
     *
     * @return bool
     */
    public function isLowercase(): bool
    {
        return $this->lowercase;
    }

    /**
     * Get the ULID time as a UNIX timestamp
     *
     * @return int
     */
    public function toTimestamp(): int
    {
        return $this->decodeTime($this->time);
    }

    /**
     * Get full ULID string
     *
     * @return string
     */
    public function toString(): string
    {
        return $this->lowercase ? strtolower($this->time . $this->randomness) : $this->time . $this->randomness;
    }

    /**
     * Alias to get a ULID string
     *
     * @return string
     */
    public function get(): string
    {
        return $this->toString();
    }

    /**
     * Return the ULID when treated as a string
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Decode the time part of the ULID
     *
     * @param string $time
     * @return integer
     */
    private function decodeTime(string $time): int
    {
        $timeChars = str_split(strrev($time));
        $carry = 0;

        foreach ($timeChars as $index => $char) {
            if (($encodingIndex = strripos(Factory::ENCODING_CHARS, $char)) === false) {
                throw new InvalidUlidStringException('Invalid ULID character: ' . $char);
            }

            $carry += ($encodingIndex * pow(Factory::ENCODING_LENGTH, $index));
        }

        if ($carry > Factory::TIME_MAX) {
            throw new InvalidUlidStringException('Invalid ULID string: timestamp too large');
        }

        return $carry;
    }
}
