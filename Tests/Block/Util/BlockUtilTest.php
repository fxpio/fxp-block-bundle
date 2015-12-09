<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Tests\Block\Util;

use Sonatra\Bundle\BlockBundle\Block\BlockInterface;
use Sonatra\Bundle\BlockBundle\Block\Util\BlockUtil;
use Sonatra\Bundle\BlockBundle\Tests\Block\Fixtures\Type\FooType;

/**
 * Block Util Test.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class BlockUtilTest extends \PHPUnit_Framework_TestCase
{
    public function testIsEmpty()
    {
        $this->assertTrue(BlockUtil::isEmpty(null));
        $this->assertTrue(BlockUtil::isEmpty(''));
        $this->assertFalse(BlockUtil::isEmpty('foobar'));
    }

    public function testCreateUniqueName()
    {
        $name = BlockUtil::createUniqueName();
        $this->assertEquals(0, strpos($name, 'block'));

        $id = substr($name, 5);
        $this->assertTrue(strlen($id) >= 5);
    }

    public function testCreateBlockId()
    {
        $parentBlock = $this->getMock('Sonatra\Bundle\BlockBundle\Block\BlockInterface');
        $parentBlock->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('foo'));

        $block = $this->getMock('Sonatra\Bundle\BlockBundle\Block\BlockInterface');
        $block->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('bar'));
        $block->expects($this->any())
            ->method('getParent')
            ->will($this->returnValue($parentBlock));
        $block->expects($this->any())
            ->method('getOption')
            ->with($this->equalTo('chained_block'))
            ->will($this->returnValue(true));

        /* @var BlockInterface $block */
        $this->assertEquals('foo_bar', BlockUtil::createBlockId($block));
    }

    public function testIsValidBlock()
    {
        $parentType = $this->getMock('Sonatra\Bundle\BlockBundle\Block\ResolvedBlockTypeInterface');
        $parentType->expects($this->any())
            ->method('getInnerType')
            ->will($this->returnValue(new FooType()));

        $blockInnerType = $this->getMock('Sonatra\Bundle\BlockBundle\Block\BlockTypeInterface');

        $blockType = $this->getMock('Sonatra\Bundle\BlockBundle\Block\ResolvedBlockTypeInterface');
        $blockType->expects($this->any())
            ->method('getInnerType')
            ->will($this->returnValue($blockInnerType));
        $blockType->expects($this->any())
            ->method('getParent')
            ->will($this->returnValue($parentType));

        $blockConfig = $this->getMock('Sonatra\Bundle\BlockBundle\Block\BlockBuilderInterface');
        $blockConfig->expects($this->any())
            ->method('getType')
            ->will($this->returnValue($blockType));

        $block = $this->getMock('Sonatra\Bundle\BlockBundle\Block\BlockInterface');
        $block->expects($this->any())
            ->method('getConfig')
            ->will($this->returnValue($blockConfig));

        /* @var BlockInterface $block */
        $this->assertTrue(BlockUtil::isBlockType($block, FooType::class));
        $this->assertTrue(BlockUtil::isBlockType($block, get_class($blockInnerType)));
        $this->assertTrue(BlockUtil::isBlockType($block, array(FooType::class, get_class($blockInnerType))));
        $this->assertTrue(BlockUtil::isBlockType($block, array(FooType::class, 'Baz')));
        $this->assertFalse(BlockUtil::isBlockType($block, 'Baz'));
        $this->assertFalse(BlockUtil::isBlockType($block, array('Baz', 'Boo!')));
    }
}
