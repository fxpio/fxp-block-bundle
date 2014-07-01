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

use Sonatra\Bundle\BlockBundle\Block\BlockBuilder;
use Sonatra\Bundle\BlockBundle\Block\BlockBuilderInterface;
use Sonatra\Bundle\BlockBundle\Block\BlockFactoryInterface;
use Sonatra\Bundle\BlockBundle\Block\DataMapperInterface;
use Sonatra\Bundle\BlockBundle\Block\ResolvedBlockTypeInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class BlockBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var BlockFactoryInterface
     */
    protected $factory;

    /**
     * @var BlockBuilderInterface
     */
    protected $builder;

    protected function setUp()
    {
        /* @var EventDispatcherInterface $dispatcher */
        $this->dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        /* @var BlockFactoryInterface $factory */
        $this->factory = $this->getMock('Sonatra\Bundle\BlockBundle\Block\BlockFactoryInterface');

        $this->builder = new BlockBuilder('name', null, $this->dispatcher, $this->factory);
    }

    protected function tearDown()
    {
        $this->dispatcher = null;
        $this->factory = null;
        $this->builder = null;
    }

    /**
     * Changing the name is not allowed, otherwise the name and property path
     * are not synchronized anymore
     *
     * @see BlockType::buildBlock
     */
    public function testNoSetName()
    {
        $this->assertFalse(method_exists($this->builder, 'setName'));
    }

    public function testAddNameNoStringAndNoInteger()
    {
        $this->setExpectedException('Sonatra\Bundle\BlockBundle\Block\Exception\UnexpectedTypeException');
        $this->builder->add(true);
    }

    public function testAddTypeNoString()
    {
        $this->setExpectedException('Sonatra\Bundle\BlockBundle\Block\Exception\UnexpectedTypeException');
        $this->builder->add('foo', 1234);
    }

    public function testAddWithGuessFluent()
    {
        $this->builder = new BlockBuilder('name', 'stdClass', $this->dispatcher, $this->factory);
        $builder = $this->builder->add('foo');
        $this->assertSame($builder, $this->builder);
    }

    public function testAddIsFluent()
    {
        $builder = $this->builder->add('foo', 'text', array('bar' => 'baz'));
        $this->assertSame($builder, $this->builder);
    }

    public function testAdd()
    {
        $this->assertFalse($this->builder->has('foo'));
        $this->builder->add('foo', 'text');
        $this->assertTrue($this->builder->has('foo'));
    }

    public function testAll()
    {
        /* @var \PHPUnit_Framework_MockObject_MockObject $factory */
        $factory = $this->factory;

        $factory->expects($this->once())
            ->method('createNamedBuilder')
            ->with('foo', 'text')
            ->will($this->returnValue(new BlockBuilder('foo', null, $this->dispatcher, $this->factory)));

        $this->assertCount(0, $this->builder->all());
        $this->assertFalse($this->builder->has('foo'));

        $this->builder->add('foo', 'text');
        $children = $this->builder->all();

        $this->assertTrue($this->builder->has('foo'));
        $this->assertCount(1, $children);
        $this->assertArrayHasKey('foo', $children);
    }

    public function testMaintainOrderOfLazyAndExplicitChildren()
    {
        $this->builder->add('foo', 'text');
        $this->builder->add($this->getBlockBuilder('bar'));
        $this->builder->add('baz', 'text');

        $children = $this->builder->all();

        $this->assertSame(array('foo', 'bar', 'baz'), array_keys($children));
    }

    public function testAddFormType()
    {
        $this->assertFalse($this->builder->has('foo'));
        $this->builder->add('foo', $this->getMock('Sonatra\Bundle\BlockBundle\Block\BlockTypeInterface'));
        $this->assertTrue($this->builder->has('foo'));
    }

    public function testRemove()
    {
        $this->builder->add('foo', 'text');
        $this->builder->remove('foo');
        $this->assertFalse($this->builder->has('foo'));
    }

    public function testRemoveUnknown()
    {
        $this->builder->remove('foo');
        $this->assertFalse($this->builder->has('foo'));
    }

    public function testRemoveAndGetForm()
    {
        $this->builder->add('foo', 'text');
        $this->builder->remove('foo');
        $block = $this->builder->getBlock();
        $this->assertInstanceOf('Sonatra\Bundle\BlockBundle\Block\Block', $block);
    }

    public function testCreateNoTypeNo()
    {
        /* @var \PHPUnit_Framework_MockObject_MockObject $factory */
        $factory = $this->factory;

        $factory->expects($this->once())
            ->method('createNamedBuilder')
            ->with('foo', 'text', null, array())
        ;

        $this->builder->create('foo');
    }

    public function testGetUnknown()
    {
        $this->setExpectedException('Sonatra\Bundle\BlockBundle\Block\Exception\InvalidArgumentException', 'The child with the name "foo" does not exist.');
        $this->builder->get('foo');
    }

    public function testGetExplicitType()
    {
        /* @var \PHPUnit_Framework_MockObject_MockObject $factory */
        $factory = $this->factory;
        $expectedType = 'text';
        $expectedName = 'foo';
        $expectedOptions = array('bar' => 'baz');

        $factory->expects($this->once())
            ->method('createNamedBuilder')
            ->with($expectedName, $expectedType, null, $expectedOptions)
            ->will($this->returnValue($this->getBlockBuilder()));

        $this->builder->add($expectedName, $expectedType, $expectedOptions);
        $builder = $this->builder->get($expectedName);

        $this->assertNotSame($builder, $this->builder);
        $this->assertSame($builder, $this->builder->get($expectedName));
    }

    public function testGetGuessedType()
    {
        /* @var \PHPUnit_Framework_MockObject_MockObject $factory */
        $factory = $this->factory;
        $expectedName = 'foo';
        $expectedOptions = array('bar' => 'baz');

        $factory->expects($this->once())
            ->method('createBuilderForProperty')
            ->with('stdClass', $expectedName, null, $expectedOptions)
            ->will($this->returnValue($this->getBlockBuilder()));

        $this->builder = new BlockBuilder('name', 'stdClass', $this->dispatcher, $this->factory);
        $this->builder->add($expectedName, null, $expectedOptions);
        $builder = $this->builder->get($expectedName);

        $this->assertNotSame($builder, $this->builder);
        $this->assertSame($builder, $this->builder->get($expectedName));
    }

    public function testGetFormConfigErasesReferences()
    {
        $builder = new BlockBuilder('name', null, $this->dispatcher, $this->factory);
        $builder->add(new BlockBuilder('child', null, $this->dispatcher, $this->factory));

        $config = $builder->getBlockConfig();
        $reflClass = new \ReflectionClass($config);
        $children = $reflClass->getProperty('children');
        $unresolvedChildren = $reflClass->getProperty('unresolvedChildren');

        $children->setAccessible(true);
        $unresolvedChildren->setAccessible(true);

        $this->assertEmpty($children->getValue($config));
        $this->assertEmpty($unresolvedChildren->getValue($config));
    }

    public function testAddChildWithoutName()
    {
        $this->builder->add(null, 'text');
        $this->assertEquals(1, $this->builder->count());
        $this->assertNotNull(array_keys($this->builder->all())[0]);
    }

    public function testGetBlockFactory()
    {
        $this->assertInstanceOf('Sonatra\Bundle\BlockBundle\Block\BlockFactoryInterface', $this->builder->getBlockFactory());
    }

    public function testGetIterator()
    {
        /* @var BlockBuilder $builder */
        $builder = $this->builder;
        $this->assertInstanceOf('ArrayIterator', $builder->getIterator());
    }

    public function testGetCount()
    {
        $this->assertEquals(0, $this->builder->count());
    }

    public function testGetBlock()
    {
        /* @var DataMapperInterface $dataMapper */
        $dataMapper = $this->getMock('Sonatra\Bundle\BlockBundle\Block\DataMapperInterface');
        /* @var ResolvedBlockTypeInterface $blockType */
        $blockType = $this->getMock('Sonatra\Bundle\BlockBundle\Block\ResolvedBlockTypeInterface');

        $builder = new BlockBuilder('name', null, $this->dispatcher, $this->factory);
        $child = new BlockBuilder('child', null, $this->dispatcher, $this->factory);

        $builder->setCompound(true);
        $builder->setDataMapper($dataMapper);
        $builder->setType($blockType);
        $child->setType($blockType);

        $builder->add($child);
        $block = $builder->getBlock();

        $this->assertInstanceOf('Sonatra\Bundle\BlockBundle\Block\Block', $block);
        $this->assertEquals(1, $block->count());
    }

    public function testGetBlockWithAutoInitialize()
    {
        /* @var BlockBuilder $builder */
        $builder = $this->builder;
        $builder->setAutoInitialize(true);
        $block = $builder->getBlock();

        $this->assertTrue($block->getConfig()->getAutoInitialize());
    }

    public function testAddTypeAfterGetBlock()
    {
        $this->setExpectedException('Sonatra\Bundle\BlockBundle\Block\Exception\BadMethodCallException');
        $config = $this->builder->getBlockConfig();
        /* @var BlockBuilder $config */
        $config->add('foo', 'text');
    }

    public function testCreateTypeAfterGetBlock()
    {
        $this->setExpectedException('Sonatra\Bundle\BlockBundle\Block\Exception\BadMethodCallException');
        $config = $this->builder->getBlockConfig();
        /* @var BlockBuilder $config */
        $config->create('foo', 'text');
    }

    public function testGetTypeAfterGetBlock()
    {
        $this->setExpectedException('Sonatra\Bundle\BlockBundle\Block\Exception\BadMethodCallException');
        $config = $this->builder->getBlockConfig();
        /* @var BlockBuilder $config */
        $config->get('foo');
    }

    public function testRemoveTypeAfterGetBlock()
    {
        $this->setExpectedException('Sonatra\Bundle\BlockBundle\Block\Exception\BadMethodCallException');
        $config = $this->builder->getBlockConfig();
        /* @var BlockBuilder $config */
        $config->remove('foo');
    }

    public function testHasTypeAfterGetBlock()
    {
        $this->setExpectedException('Sonatra\Bundle\BlockBundle\Block\Exception\BadMethodCallException');
        $config = $this->builder->getBlockConfig();
        /* @var BlockBuilder $config */
        $config->has('foo');
    }

    public function testAllTypeAfterGetBlock()
    {
        $this->setExpectedException('Sonatra\Bundle\BlockBundle\Block\Exception\BadMethodCallException');
        $config = $this->builder->getBlockConfig();
        /* @var BlockBuilder $config */
        $config->all();
    }

    public function testCountTypeAfterGetBlock()
    {
        $this->setExpectedException('Sonatra\Bundle\BlockBundle\Block\Exception\BadMethodCallException');
        $config = $this->builder->getBlockConfig();
        /* @var BlockBuilder $config */
        $config->count();
    }

    public function testGetBlockTypeAfterGetBlock()
    {
        $this->setExpectedException('Sonatra\Bundle\BlockBundle\Block\Exception\BadMethodCallException');
        $config = $this->builder->getBlockConfig();
        /* @var BlockBuilder $config */
        $config->getBlock();
    }

    public function testGetIteratorTypeAfterGetBlock()
    {
        $this->setExpectedException('Sonatra\Bundle\BlockBundle\Block\Exception\BadMethodCallException');
        /* @var BlockBuilder $config */
        $config = $this->builder->getBlockConfig();
        $config->getIterator();
    }

    private function getBlockBuilder($name = 'name')
    {
        $mock = $this->getMockBuilder('Sonatra\Bundle\BlockBundle\Block\BlockBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($name));

        return $mock;
    }
}
