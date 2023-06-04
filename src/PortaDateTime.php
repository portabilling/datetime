<?php

/*
 * Library to handle PortaOne billing datetime and date strings as objects
 */

use Psr\Container\ContainerInterface;

/**
 * Immutable class to handle PortaOne billing datetime and date strings as objects
 *
 * Portaone API always use UTC datetime while local timezone is essential to show/set
 * datetime at the business-logic level.
 *
 * In addition, some specific task appears like to set correct addon product change
 * overe midnight at local time: last second of one day to end one product and
 * 00:00:00 of the next day for the new product.
 *
 * This class extends [DateTimeImmutable](https://www.php.net/manual/en/class.datetimeimmutable.php) with
 * simple way to translate between porta-formatted UTC strings and DateTime objects
 * in local timezone plus some useful functions to set time at specific boundaries.
 *
 * Immutable means call of any modification method returns a new instance of the class.
 *
 * Also, mind that default timezone may be defined in [PSR-11 container](https://www.php-fig.org/psr/psr-11/), see `__construct()` below.
 *
 * See the [usage example](https://github.com/portabilling/datetime/blob/master/examples/UsageExample.php)
 *
 * @api
 */
class PortaDateTime extends DateTimeImmutable {

    /**
     * Standart Porta format for date-time string
     * @api
     */
    const PORTA_DATETIME = 'Y-m-d H:i:s';

    /**
     * Standart Porta format for date-only string
     * @api
     */
    const PORTA_DATE = 'Y-m-d';

    /**
     * Default timezone tag to retrieve default timezone from PSR-11 container
     * @api
     */
    const DEFAULT_TIMEZONE_TAG = 'default.timezone';

    /**
     * Setup the class
     *
     * The same as standard [DateTimeImmutable](https://www.php.net/manual/en/class.datetimeimmutable.php),
     * but may accept timezone very different ways either [DateTimeZone](https://www.php.net/manual/en/class.datetimezone)
     * class or just a timezone string.
     *
     * @param string $datetime The same as basic DateTime parameter
     * @param string|DateTimeZone|ContainerInterface $timezone Timezone may be supplied
     * different ways:
     * - By providing [DateTimeZone](https://www.php.net/manual/en/class.datetimezone)
     * object, same as base class
     * - By providig string, then DateTimeZone object will be created using this
     * string as timezone argument
     * - By providing PSR-11 container object, then constructor will use get()
     * method to retrieve DateTimeZone object **or** timezone string from the
     * container. See $tomezoneTag info below. If it failed to retrieve timezone
     * from container - it will fallbeck to UTC silently.
     * @param string $timezoneTag optional, only used if PSR-11 container given
     * as timezone argument to use as `$timezone->get($timezoneTag)`. If omit,
     * class constant {@see \PortaDateTime::DEFAULT_TIMEZONE_TAG} default tag be used.
     * @api
     */
    public function __construct(
            string $datetime = "now",
            $timezone = "UTC",
            string $timezoneTag = self::DEFAULT_TIMEZONE_TAG) {
        parent::__construct($datetime, self::prepreTimezone($timezone, $timezoneTag));
    }

    /**
     * Creates PortaDateTime object from Porta-formatted string
     *
     * Creates object from Portaone datetime string with desired timezone.
     * Please, mind the billing always returns datetime in UTC.
     *
     * @param string $datetime - the datetime string as you got it form billing
     * @param string|DateTimeZone|ContainerInterface $timezone Timezone may be supplied
     * different ways:
     * - By providing [DateTimeZone](https://www.php.net/manual/en/class.datetimezone)
     * object, same as base class
     * - By providig string, then DateTimeZone object will be created using this
     * string as timezone argument
     * - By providing PSR-11 container object, then constructor will use get()
     * method to retrieve DateTimeZone object **or** timezone string from the
     * container. See $tomezoneTag info below. If it failed to retrieve timezone
     * from container - it will fallbeck to UTC silently.
     * @param string $timezoneTag optional, only used if PSR-11 container given
     * as timezone argument to use as `$timezone->get($timezoneTag)`. If omit,
     * class constant {@see \PortaDateTime::DEFAULT_TIMEZONE_TAG} default tag be used.
     * @return PortaDateTime
     * @api
     */
    public static function fromPortaString(
            string $datetime,
            $timezone = 'UTC',
            string $timezoneTag = self::DEFAULT_TIMEZONE_TAG): self {
        return (new PortaDateTime($datetime, 'UTC'))
                        ->setTimezone(self::prepreTimezone($timezone, $timezoneTag));
    }

    /**
     * Creates PortaDateTime object from Portaone date-only string
     *
     * Creates object from Portaone date-only string with time is set to zero
     * in desired timezone. The difference to create from full datetime porta string
     * is that date string in the billing always related to context-local
     * (example - customer) timzone, then need to be set right way.
     *
     * @param string $date - the date string as you got it form billing
     * @param string|DateTimeZone|ContainerInterface $timezone Timezone may be supplied
     * different ways:
     * - By providing [DateTimeZone](https://www.php.net/manual/en/class.datetimezone)
     * object, same as base class
     * - By providig string, then DateTimeZone object will be created using this
     * string as timezone argument
     * - By providing PSR-11 container object, then constructor will use get()
     * method to retrieve DateTimeZone object **or** timezone string from the
     * container. See $tomezoneTag info below. If it failed to retrieve timezone
     * from container - it will fallbeck to UTC silently.
     * @param string $timezoneTag optional, only used if PSR-11 container given
     * as timezone argument to use as `$timezone->get($timezoneTag)`. If omit,
     * class constant {@see \PortaDateTime::DEFAULT_TIMEZONE_TAG} default tag be used.
     * @return PortaDateTime
     * @api
     */
    public static function fromPortaDateString(
            string $date,
            $timezone = 'UTC',
            string $timezoneTag = self::DEFAULT_TIMEZONE_TAG): self {
        return (new PortaDateTime($date, self::prepreTimezone($timezone, $timezoneTag)));
    }

    /**
     * Create PortaDateTime object from any DateTimeInterface object
     *
     * Timezone of the jbject will match timezone of the given source
     *
     * @param DateTimeInterface $object to convert to PortaDateTime object
     * @return PotrtaDateTime
     * @api
     */
    public static function fromInterface(\DateTimeInterface $object): self {
        return (new PortaDateTime('@' . $object->getTimestamp(), $object->getTimezone()));
    }

    /**
     * Return Porta-formatted datetime string at UTC timezone
     *
     * Shift timezone to UTC and format to Porta format datetime string.
     *
     * @return string Porta-formatted datetime string in UTC zone
     * @api
     */
    public function formatPorta(): string {
        return $this->setTimezone(new \DateTimeZone('UTC'))
                        ->format(self::PORTA_DATETIME);
    }

    /**
     * Return Porta-formatted datetime string at UTC timezone
     *
     * Shift timezone to UTC and format to Porta format datetime string.
     *
     * @return string Porta-formatted datetime string in UTC zone
     */
    public function __toString() {
        return $this->formatPorta();
    }

    /**
     * Set time to the first moment of the day in the current timezone
     *
     * @return PortaDateTime new object with applied changes
     * @api
     */
    public function firstMoment(): self {
        return $this->setTime(0, 0, 0);
    }

    /**
     * Set time to the last moment of the day (23:59:59) in the current timezone
     *
     * @return PortaDateTime new object with applied changes
     * @api
     */
    public function lastMoment(): self {
        return $this->setTime(23, 59, 59);
    }

    /**
     * Return object with one day later than this one
     *
     * @return PortaDateTime new object with applied changes
     * @api
     */
    public function nextDay(): self {
        return $this->modify('+1 day');
    }

    /**
     * return object which set to the fist day of the next month
     *
     * @return PortaDateTime new object with applied changes
     * @api
     */
    public function firstDayNextMonth(): self {
        return $this->modify('first day of next month');
    }

    /**
     * Return object which set to the last day of current object's month
     *
     * @return PortaDateTime new object with applied changes
     * @api
     */
    public function lastDayThisMonth(): self {
        return $this->modify('last day of this month');
    }

    /**
     * Calculates prorated value from given date till the end of the month
     *
     * @param float $fee Basic rate to prorate
     * @return float Prorated value
     * @api
     */
    public function prorateTillEndOfMonth(float $fee): float {
        $days = (int) $this->format('t');
        return round($days - $this->format('j') + 1) * $fee / $days;
    }

    /**
     * Checks if the datetime in the future or not
     *
     * @return bool true if datetime in the future
     * @api
     */
    public function inFuture(): bool {
        return $this > (new DateTime());
    }

    /**
     * Checks if the datetime in the past or not
     *
     * @return bool true if datetime in the future
     * @api
     */
    public function inPast(): bool {
        return $this < (new DateTime());
    }

    /**
     * Return true if object datetime is between $from and $to
     *
     * If $from or $to set to null, it means there no limit on the side.
     *
     * @param PortaDateTime|null $from
     * @param PortaDateTime|null $to
     * @return bool
     * @api
     */
    public function between(?DateTimeInterface $from, ?DateTimeInterface $to): bool {
        return (is_null($from) || ($this >= $from)) && (is_null($to) || ($this <= $to));
    }

    /**
     * Returns Portaone-format datetime string in UTC for any DateTimeInterface
     *
     * @param DateTimeInterface $datetime
     * @return string
     * @api
     */
    public static function formatDateTime(DateTimeInterface $datetime): string {
        return self::fromInterface($datetime)
                        ->setTimezone(new DateTimeZone('UTC'))
                        ->format(self::PORTA_DATETIME);
    }

    protected static function prepreTimezone($timezone, $timezoneTag): DateTimeZone {
        if ($timezone instanceof ContainerInterface) {
            $container = $timezone;
            if ($container->has($timezoneTag)) {
                $timezone = $container->get($timezoneTag);
            } else {
                $timezone = 'UTC';
            }
        }
        if (is_string($timezone)) {
            return new \DateTimeZone($timezone);
        } elseif ($timezone instanceof \DateTimeZone) {
            return $timezone;
        } else {
            throw new InvalidArgumentException("Timezone must be a string or a DateTimeZone object");
        }
    }

}
