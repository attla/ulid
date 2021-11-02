<?php

namespace Attla\Ulid;

use Attla\Ulid\Exception\InvalidUlidStringException;

class Factory
{
    /**
     * Encoding chars
     *
     * @var string
     */
    public const ENCODING_CHARS = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';

    /**
     * Encoding chars length
     *
     * @var int
     */
    public const ENCODING_LENGTH = 32;

    /**
     * ULID time max value
     *
     * @var int
     */
    public const TIME_MAX = 281474976710655;

    /**
     * ULID time part length
     *
     * @var int
     */
    public const TIME_LENGTH = 10;

    /**
     * ULID random part length
     *
     * @var int
     */
    public const RANDOM_LENGTH = 16;

    /**
     * Last time the ULID was generated
     *
     * @var int
     */
    private static $lastGenTime = 0;

    /**
     * Last random chars generated
     *
     * @var array
     */
    private static $lastRandChars = [];

    /**
     * Create a ULID from string
     *
     * @param string $value The ULID string
     * @param boolean $lowercase True to output lowercase ULIDs
     * @return self
     */
    public static function fromString(string $value, bool $lowercase = false): self
    {
        $ulidLength = static::TIME_LENGTH + static::RANDOM_LENGTH;

        if (strlen($value) !== $ulidLength) {
            throw new InvalidUlidStringException('Invalid ULID string (wrong length): ' . $value);
        }

        // Convert to uppercase for regex. Doesn't matter for output later, that is determined by $lowercase.
        $value = strtoupper($value);

        if (!preg_match(sprintf('!^[%s]{%d}$!', static::ENCODING_CHARS, $ulidLength), $value)) {
            throw new InvalidUlidStringException('Invalid ULID string (wrong characters): ' . $value);
        }

        return new Ulid(substr($value, 0, static::TIME_LENGTH), substr($value, static::TIME_LENGTH, static::RANDOM_LENGTH), $lowercase);
    }

    /**
     * Create a ULID using the given timestamp
     *
     * @param int $milliseconds Number of milliseconds since the UNIX epoch for which to generate this ULID
     * @param bool $lowercase True to output lowercase ULIDs
     * @return Ulid Returns a ULID object for the given microsecond time
     */
    public static function fromTimestamp(int $milliseconds, bool $lowercase = false): self
    {
        $duplicateTime = $milliseconds === static::$lastGenTime;

        static::$lastGenTime = $milliseconds;

        $timeChars = '';
        $randChars = '';

        $encodingChars = static::ENCODING_CHARS;

        for ($i = static::TIME_LENGTH - 1; $i >= 0; $i--) {
            $mod = $milliseconds % static::ENCODING_LENGTH;
            $timeChars = $encodingChars[$mod] . $timeChars;
            $milliseconds = ($milliseconds - $mod) / static::ENCODING_LENGTH;
        }

        if (!$duplicateTime) {
            for ($i = 0; $i < static::RANDOM_LENGTH; $i++) {
                static::$lastRandChars[$i] = random_int(0, 31);
            }
        } else {
            // If the timestamp hasn't changed since last push,
            // use the same random number, except incremented by 1.
            for ($i = static::RANDOM_LENGTH - 1; $i >= 0 && static::$lastRandChars[$i] === 31; $i--) {
                static::$lastRandChars[$i] = 0;
            }

            static::$lastRandChars[$i]++;
        }

        for ($i = 0; $i < static::RANDOM_LENGTH; $i++) {
            $randChars .= $encodingChars[static::$lastRandChars[$i]];
        }

        return new Ulid($timeChars, $randChars, $lowercase);
    }

    /**
     * Create a ULID using the current time
     *
     * @param boolean $lowercase True to output lowercase ULIDs
     * @return self
     */
    public static function generate(bool $lowercase = false): self
    {
        return static::fromTimestamp((int) (microtime(true) * 1000), $lowercase);
    }
}
