<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Block\Extension\Core\DataMapper;

use Sonatra\Bundle\BlockBundle\Block\DataMapperInterface;

/**
 * Lets have a compound block with a data mapper not doing any work by default.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class WrapperMapper implements DataMapperInterface
{
    /**
     * {@inheritdoc}
     */
    public function mapDataToViews($data, array $blocks)
    {
    }
}
