<?php

namespace ACPT\Core\Helper;

class Id
{
    const UUID_V1  = 'uuid_v1';
    const UUID_V4  = 'uuid_v4';
    const UUID_V7  = 'uuid_v7';
    const ULID     = 'ulid';
    const AUTO_INC = 'auto_inc';
    const RANDOM   = 'random';

    /**
     * @param string $strategy
     *
     * @return string
     */
    public static function formatStrategy($strategy = self::UUID_V1)
    {
        switch ($strategy){
            default:
            case self::UUID_V1:
                return "UUID(v1)";

            case self::UUID_V4:
                return "UUID(v4)";

            case self::UUID_V7:
                return "UUID(v7)";

            case self::ULID:
                return "ULID";

            case self::AUTO_INC:
                return "AUTO_INCREMENT";

            case self::RANDOM:
                return "RANDOM";
        }
    }

    /**
     * Check if value is valid
     *
     * @param $value
     * @param $strategy
     *
     * @return bool
     */
    public static function isValid($value, $strategy)
    {
        switch ($strategy){
            default:
            case self::UUID_V1:
                return Uuid::isV1Valid($value);

            case self::UUID_V4:
                return Uuid::isV4Valid($value);

            case self::UUID_V7:
                return Uuid::isV7Valid($value);

            case self::ULID:
                return Ulid::isValid($value);

            case self::AUTO_INC:
                return is_numeric($value);

            case self::RANDOM:
                return strlen((string)$value) === 11 and is_numeric($value);
        }
    }

    /**
     * @param string                  $strategy
     * @param null                    $index
     * @param \DateTimeInterface|null $dateTime
     *
     * @return int|string|null
     * @throws \Exception
     */
    public static function generate($strategy = self::UUID_V1, $index = null, ?\DateTimeInterface $dateTime = null)
    {
        switch ($strategy){
            default:
            case self::UUID_V1:
                return Uuid::v1($dateTime ?? new \DateTime());

            case self::UUID_V4:
                return Uuid::v4();

            case self::UUID_V7:
                return Uuid::v7($dateTime ?? new \DateTime());

            case self::ULID:
                return Ulid::generate($dateTime ?? new \DateTime());

            case self::AUTO_INC:
                return $index ? ($index+1) : 1;

            case self::RANDOM:
                try {
                    return random_int(11111111111, 99999999999);
                } catch (\Exception $exception){
                    return mt_rand(11111111111, 99999999999);
                }
        }
    }
}