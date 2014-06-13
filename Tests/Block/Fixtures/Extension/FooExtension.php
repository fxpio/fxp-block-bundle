<?php

/**
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Tests\Block\Fixtures\Extension;

use Sonatra\Bundle\BlockBundle\Block\AbstractTypeExtension;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class FooExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return 'foo';
    }
}
