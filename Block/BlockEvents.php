<?php

/**
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Block;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
final class BlockEvents
{
    const PRE_SET_DATA = 'block.pre_set_data';

    const POST_SET_DATA = 'block.post_set_data';

    private function __construct()
    {
    }
}
