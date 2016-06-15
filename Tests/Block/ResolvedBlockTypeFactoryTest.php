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

use Sonatra\Bundle\BlockBundle\Block\BlockTypeInterface;
use Sonatra\Bundle\BlockBundle\Block\ResolvedBlockTypeFactory;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ResolvedBlockTypeFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateResolvedType()
    {
        /* @var BlockTypeInterface|\PHPUnit_Framework_MockObject_MockObject $type */
        $type = $this->getMockBuilder('Sonatra\Bundle\BlockBundle\Block\BlockTypeInterface')->getMock();

        $parentType = $this->getMockBuilder('Sonatra\Bundle\BlockBundle\Block\ResolvedBlockTypeInterface')->getMock();

        $factory = new ResolvedBlockTypeFactory();
        $rType = $factory->createResolvedType($type, array(), $parentType);

        $this->assertInstanceOf('Sonatra\Bundle\BlockBundle\Block\ResolvedBlockTypeInterface', $rType);
    }
}
