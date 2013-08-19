<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Block\Extension\Core\DataTransformer;

use Sonatra\Bundle\BlockBundle\Block\DataTransformerInterface;
use Sonatra\Bundle\BlockBundle\Block\Exception\TransformationFailedException;
use Sonatra\Bundle\BlockBundle\Block\Exception\UnexpectedTypeException;

/**
 * Transforms between a number type and a localized number with grouping
 * (each thousand) and comma separators.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class NumberToLocalizedStringTransformer implements DataTransformerInterface
{
    /**
     * Rounds a number towards positive infinity.
     *
     * Rounds 1.4 to 2 and -1.4 to -1.
     */
    const ROUND_CEILING = \NumberFormatter::ROUND_CEILING;

    /**
     * Rounds a number towards negative infinity.
     *
     * Rounds 1.4 to 1 and -1.4 to -2.
     */
    const ROUND_FLOOR = \NumberFormatter::ROUND_FLOOR;

    /**
     * Rounds a number away from zero.
     *
     * Rounds 1.4 to 2 and -1.4 to -2.
     */
    const ROUND_UP = \NumberFormatter::ROUND_UP;

    /**
     * Rounds a number towards zero.
     *
     * Rounds 1.4 to 1 and -1.4 to -1.
     */
    const ROUND_DOWN = \NumberFormatter::ROUND_DOWN;

    /**
     * Rounds to the nearest number and halves to the next even number.
     *
     * Rounds 2.5, 1.6 and 1.5 to 2 and 1.4 to 1.
     */
    const ROUND_HALF_EVEN = \NumberFormatter::ROUND_HALFEVEN;

    /**
     * Rounds to the nearest number and halves away from zero.
     *
     * Rounds 2.5 to 3, 1.6 and 1.5 to 2 and 1.4 to 1.
     */
    const ROUND_HALF_UP = \NumberFormatter::ROUND_HALFUP;

    /**
     * Rounds to the nearest number and halves towards zero.
     *
     * Rounds 2.5 and 1.6 to 2, 1.5 and 1.4 to 1.
     */
    const ROUND_HALF_DOWN = \NumberFormatter::ROUND_HALFDOWN;

    protected $precision;

    protected $grouping;

    protected $roundingMode;

    protected $locale;

    /**
     * Constructor.
     *
     * @param integer $precision
     * @param boolean $grouping
     * @param integer $roundingMode
     * @param string  $locale
     */
    public function __construct($precision = null, $grouping = null, $roundingMode = null, $locale = null)
    {
        if (null === $grouping) {
            $grouping = false;
        }

        if (null === $roundingMode) {
            $roundingMode = self::ROUND_HALF_EVEN;
        }

        if (null === $locale) {
            $locale = \Locale::getDefault();
        }

        $this->precision = $precision;
        $this->grouping = $grouping;
        $this->roundingMode = $roundingMode;
        $this->locale = $locale;
    }

    /**
     * Transforms a number type into localized number.
     *
     * @param integer|float $value Number value.
     *
     * @return string Localized value.
     *
     * @throws UnexpectedTypeException       if the given value is not numeric
     * @throws TransformationFailedException if the value can not be transformed
     */
    public function transform($value)
    {
        if (null === $value) {
            return '';
        }

        if (!is_numeric($value)) {
            throw new UnexpectedTypeException($value, 'numeric');
        }

        $formatter = $this->getNumberFormatter();
        $value = $formatter->format($value);

        if (intl_is_failure($formatter->getErrorCode())) {
            throw new TransformationFailedException($formatter->getErrorMessage());
        }

        return $value;
    }

    /**
     * Returns a preconfigured \NumberFormatter instance
     *
     * @param integer $style
     *
     * @return \NumberFormatter
     */
    protected function getNumberFormatter($style = \NumberFormatter::DECIMAL)
    {
        $formatter = new \NumberFormatter($this->locale, $style);

        if (null !== $this->precision) {
            $formatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, $this->precision);
            $formatter->setAttribute(\NumberFormatter::ROUNDING_MODE, $this->roundingMode);
        }

        $formatter->setAttribute(\NumberFormatter::GROUPING_USED, $this->grouping);

        return $formatter;
    }
}
