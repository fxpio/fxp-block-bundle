<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\BlockBundle\Tests\Block\Fixtures\Extension;

use Fxp\Component\Block\AbstractTypeExtension;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class BarExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes()
    {
        return [];
    }
}
