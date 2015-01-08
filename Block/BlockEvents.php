<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Block;

use Sonatra\Bundle\BlockBundle\Block\Exception\ClassNotInstantiableException;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
final class BlockEvents
{
    /**
     * @Event
     */
    const PRE_SET_DATA = 'block.pre_set_data';

    /**
     * @Event
     */
    const POST_SET_DATA = 'block.post_set_data';

    public function __construct()
    {
        throw new ClassNotInstantiableException(__CLASS__);
    }
}
