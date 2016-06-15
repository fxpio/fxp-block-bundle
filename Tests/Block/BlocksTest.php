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

use Sonatra\Bundle\BlockBundle\Block\Blocks;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class BlocksTest extends \PHPUnit_Framework_TestCase
{
    public function testObjectFactoryBuilderCreator()
    {
        $bf = Blocks::createBlockFactoryBuilder();

        $this->assertInstanceOf('Sonatra\Bundle\BlockBundle\Block\BlockFactoryBuilderInterface', $bf);
    }

    public function testObjectFactoryCreator()
    {
        $bf = Blocks::createBlockFactory();

        $this->assertInstanceOf('Sonatra\Bundle\BlockBundle\Block\BlockFactoryInterface', $bf);
    }

    /**
     * @expectedException \Sonatra\Bundle\BlockBundle\Block\Exception\RuntimeException
     */
    public function testInstantiationOfClass()
    {
        new Blocks();
    }
}
