<?php

/**
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Tests\Block;

use Sonatra\Bundle\BlockBundle\Block\BlockFactoryInterface;
use Sonatra\Bundle\BlockBundle\Block\BlockTypeInterface;
use Sonatra\Bundle\BlockBundle\Block\ResolvedBlockType;
use Sonatra\Bundle\BlockBundle\Tests\Block\Fixtures\Extension\FooExtension;
use Sonatra\Bundle\BlockBundle\Tests\Block\Fixtures\Type\FooSubType;
use Sonatra\Bundle\BlockBundle\Tests\Block\Fixtures\Type\FooType;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ResolvedBlockTypeTest extends \PHPUnit_Framework_TestCase
{
    public function testClassUnexist()
    {
        $this->setExpectedException('Sonatra\Bundle\BlockBundle\Block\Exception\InvalidArgumentException');

        $type = $this->getMock('Sonatra\Bundle\BlockBundle\Block\BlockTypeInterface');
        $type->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('foo@bar'));

        /* @var BlockTypeInterface $type */
        new ResolvedBlockType($type);
    }

    public function testWrongExtensions()
    {
        $this->setExpectedException('Sonatra\Bundle\BlockBundle\Block\Exception\UnexpectedTypeException');

        new ResolvedBlockType(new FooType(), array('wrong_extension'));
    }

    public function testBasicOperations()
    {
        $parentType = new FooSubType();
        $type = new FooType();
        $rType = new ResolvedBlockType($type, array(new FooExtension()), new ResolvedBlockType($parentType));

        $this->assertEquals($type->getName(), $rType->getName());
        $this->assertInstanceOf('Sonatra\Bundle\BlockBundle\Block\ResolvedBlockTypeInterface', $rType->getParent());
        $this->assertEquals($type, $rType->getInnerType());

        $exts = $rType->getTypeExtensions();
        $this->assertTrue(is_array($exts));
        $this->assertCount(1, $exts);

        $options = $rType->getOptionsResolver();
        $this->assertInstanceOf('Symfony\Component\OptionsResolver\OptionsResolver', $options);
    }

    public function testBuildBlockAndBuildView()
    {
        $type = new FooType();
        $parentType = new FooSubType();
        $rType = new ResolvedBlockType($type, array(new FooExtension()), new ResolvedBlockType($parentType));

        /* @var BlockFactoryInterface $factory */
        $factory = $this->getMock('Sonatra\Bundle\BlockBundle\Block\BlockFactoryInterface');
        $builder = $rType->createBuilder($factory, 'name');

        $this->assertInstanceOf('Sonatra\Bundle\BlockBundle\Block\BlockBuilderInterface', $builder);
        $this->assertEquals($rType, $builder->getType());

        $rType->buildBlock($builder, $builder->getOptions());
        $rType->finishBlock($builder, $builder->getOptions());

        $block = $builder->getBlock();
        $view = $rType->createView($block);
        $this->assertInstanceOf('Sonatra\Bundle\BlockBundle\Block\BlockView', $view);

        $rType->buildView($view, $block, $block->getOptions());
        $rType->finishView($view, $block, $block->getOptions());
    }

    public function testAddChildAndRemoveChild()
    {
        $type = new FooType();
        $parentType = new FooSubType();
        $rType = new ResolvedBlockType($type, array(new FooExtension()), new ResolvedBlockType($parentType));

        /* @var BlockFactoryInterface $factory */
        $factory = $this->getMock('Sonatra\Bundle\BlockBundle\Block\BlockFactoryInterface');
        $block1 = $rType->createBuilder($factory, 'name1')->getBlock();
        $block2 = $rType->createBuilder($factory, 'name2')->getBlock();

        $this->assertInstanceOf('Sonatra\Bundle\BlockBundle\Block\BlockBuilderInterface', $block1->getConfig());
        $this->assertInstanceOf('Sonatra\Bundle\BlockBundle\Block\BlockBuilderInterface', $block2->getConfig());

        $rType->addParent($block1, $block2, $block1->getOptions());
        $rType->addChild($block2, $block1, $block2->getOptions());

        $rType->removeChild($block2, $block1, $block2->getOptions());
        $rType->removeParent($block1, $block2, $block1->getOptions());

        $view1 = $rType->createView($block1);
        $view2 = $rType->createView($block2);

        $this->assertInstanceOf('Sonatra\Bundle\BlockBundle\Block\BlockView', $view1);
        $this->assertInstanceOf('Sonatra\Bundle\BlockBundle\Block\BlockView', $view2);

        $rType->buildView($view1, $block1, $block1->getOptions());
        $rType->finishView($view1, $block1, $block1->getOptions());
        $rType->buildView($view2, $block2, $block2->getOptions());
        $rType->finishView($view2, $block2, $block2->getOptions());
    }

    public function testAbstractType()
    {
        /* @var \Sonatra\Bundle\BlockBundle\Block\AbstractType $type */
        $type = $this->getMockForAbstractClass('Sonatra\Bundle\BlockBundle\Block\AbstractType');
        $this->assertEquals('block', $type->getParent());
    }
}
