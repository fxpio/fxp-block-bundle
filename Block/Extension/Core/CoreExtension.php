<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Block\Extension\Core;

use Sonatra\Bundle\BlockBundle\Block\AbstractExtension;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Represents the main block extension, which loads the core functionality.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class CoreExtension extends AbstractExtension
{
    protected function loadTypes()
    {
        return array(
            new Type\BlockType(PropertyAccess::createPropertyAccessor()),
            new Type\BirthdayType(),
            new Type\CheckboxType(),
            new Type\ChoiceType(),
            new Type\CollectionType(),
            new Type\CountryType(),
            new Type\DateType(),
            new Type\DateTimeType(),
            new Type\EmailType(),
            new Type\HiddenType(),
            new Type\IntegerType(),
            new Type\LanguageType(),
            new Type\LocaleType(),
            new Type\MoneyType(),
            new Type\NumberType(),
            new Type\PasswordType(),
            new Type\PercentType(),
            new Type\RadioType(),
            new Type\RepeatedType(),
            new Type\TextareaType(),
            new Type\TextType(),
            new Type\TimeType(),
            new Type\TimezoneType(),
            new Type\UrlType(),
        );
    }
}
