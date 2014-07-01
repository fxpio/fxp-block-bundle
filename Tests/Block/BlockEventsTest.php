<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Tests\Block;

use Sonatra\Bundle\BlockBundle\Block\BlockEvents;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class BlockEventsTest extends \PHPUnit_Framework_TestCase
{
    public function testInstantiationOfClass()
    {
        $this->setExpectedException('Sonatra\Bundle\BlockBundle\Block\Exception\RuntimeException');

        new BlockEvents();
    }
}
