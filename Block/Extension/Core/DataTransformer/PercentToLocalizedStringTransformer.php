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

use Sonatra\Bundle\BlockBundle\Block\Exception\TransformationFailedException;
use Sonatra\Bundle\BlockBundle\Block\Exception\UnexpectedTypeException;

/**
 * Transforms between a number type and a localized number with grouping
 * (each thousand) and comma separators and percent symbol.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PercentToLocalizedStringTransformer extends NumberToLocalizedStringTransformer
{
    /**
     * Transforms a number type into localized percent.
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

        $formatter = $this->getNumberFormatter(\NumberFormatter::PERCENT);
        $value = $formatter->format($value);

        if (intl_is_failure($formatter->getErrorCode())) {
            throw new TransformationFailedException($formatter->getErrorMessage());
        }

        return $value;
    }
}
