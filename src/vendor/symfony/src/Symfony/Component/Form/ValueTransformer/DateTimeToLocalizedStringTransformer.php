<?php

namespace Symfony\Component\Form\ValueTransformer;

use \Symfony\Component\Form\ValueTransformer\ValueTransformerException;

/**
 * Transforms between a normalized time and a localized time string
 *
 * Options:
 *
 *  * "input": The type of the normalized format ("time" or "timestamp"). Default: "datetime"
 *  * "output": The type of the transformed format ("string" or "array"). Default: "string"
 *  * "format": The format of the time string ("short", "medium", "long" or "full"). Default: "short"
 *  * "locale": The locale of the localized string. Default: Result of Locale::getDefault()
 *
 * @author Bernhard Schussek <bernhard.schussek@symfony-project.com>
 * @author Florian Eckerstorfer <florian@eckerstorfer.org>
 */
class DateTimeToLocalizedStringTransformer extends BaseDateTimeTransformer
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        parent::configure();

        $this->addOption('date_format', self::MEDIUM);
        $this->addOption('time_format', self::SHORT);
        $this->addOption('input_timezone', 'UTC');
        $this->addOption('output_timezone', 'UTC');

        if (!in_array($this->getOption('date_format'), self::$formats, true)) {
            throw new \InvalidArgumentException(sprintf('The option "date_format" is expected to be one of "%s". Is "%s"', implode('", "', self::$formats), $this->getOption('time_format')));
        }

        if (!in_array($this->getOption('time_format'), self::$formats, true)) {
            throw new \InvalidArgumentException(sprintf('The option "time_format" is expected to be one of "%s". Is "%s"', implode('", "', self::$formats), $this->getOption('time_format')));
        }
    }

    /**
     * Transforms a normalized date into a localized date string/array.
     *
     * @param  DateTime $dateTime  Normalized date.
     * @return string|array        Localized date string/array.
     */
    public function transform($dateTime)
    {
        if (!$dateTime instanceof \DateTime) {
            throw new \InvalidArgumentException('Expected value of type \DateTime');
        }

        $inputTimezone = $this->getOption('input_timezone');

        // convert time to UTC before passing it to the formatter
        if ($inputTimezone != 'UTC') {
            $dateTime->setTimezone(new \DateTimeZone('UTC'));
        }

        $value = $this->getIntlDateFormatter()->format((int)$dateTime->format('U'));

        if (intl_get_error_code() != 0) {
            throw new TransformationFailedException(intl_get_error_message());
        }

        return $value;
    }

    /**
     * Transforms a localized date string/array into a normalized date.
     *
     * @param  string|array $value Localized date string/array
     * @return DateTime Normalized date
     */
    public function reverseTransform($value)
    {
        $inputTimezone = $this->getOption('input_timezone');

        if (!is_string($value)) {
            throw new \InvalidArgumentException(sprintf('Expected argument of type string, %s given', gettype($value)));
        }

        $timestamp = $this->getIntlDateFormatter()->parse($value);

        if (intl_get_error_code() != 0) {
            throw new TransformationFailedException(intl_get_error_message());
        }

        // read timestamp into DateTime object - the formatter delivers in UTC
        $dateTime = new \DateTime(sprintf('@%s UTC', $timestamp));

        if ($inputTimezone != 'UTC') {
            $dateTime->setTimezone(new \DateTimeZone($inputTimezone));
        }

        return $dateTime;
    }

    /**
     * Returns a preconfigured IntlDateFormatter instance
     *
     * @return \IntlDateFormatter
     */
    protected function getIntlDateFormatter()
    {
        $dateFormat = $this->getIntlFormatConstant($this->getOption('date_format'));
        $timeFormat = $this->getIntlFormatConstant($this->getOption('time_format'));
        $timezone = $this->getOption('output_timezone');

        return new \IntlDateFormatter($this->locale, $dateFormat, $timeFormat, $timezone);
    }
}