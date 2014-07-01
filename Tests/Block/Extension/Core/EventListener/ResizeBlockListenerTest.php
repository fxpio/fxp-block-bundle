<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Tests\Block\Extension\Core\EventListener;

use Sonatra\Bundle\BlockBundle\Block\BlockEvents;
use Sonatra\Bundle\BlockBundle\Block\BlockFactoryInterface;
use Sonatra\Bundle\BlockBundle\Block\BlockInterface;
use Sonatra\Bundle\BlockBundle\Block\DataMapperInterface;
use Sonatra\Bundle\BlockBundle\Block\Extension\Core\EventListener\ResizeBlockListener;
use Sonatra\Bundle\BlockBundle\Block\BlockBuilder;
use Sonatra\Bundle\BlockBundle\Block\BlockEvent;
use Sonatra\Bundle\BlockBundle\Block\ResolvedBlockTypeInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ResizeBlockListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dispatcher;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $factory;
    /**
     * @var BlockInterface
     */
    protected $block;

    protected function setUp()
    {
        /* @var DataMapperInterface $dataMapper */
        $dataMapper = $this->getDataMapper();

        $this->dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->factory = $this->getMock('Sonatra\Bundle\BlockBundle\Block\BlockFactoryInterface');
        $this->block = $this->getBuilder()
            ->setCompound(true)
            ->setDataMapper($dataMapper)
            ->getBlock();
    }

    protected function tearDown()
    {
        $this->dispatcher = null;
        $this->factory = null;
        $this->block = null;
    }

    protected function getBuilder($name = 'name')
    {
        /* @var EventDispatcherInterface $dispatcher */
        $dispatcher = $this->dispatcher;
        /* @var BlockFactoryInterface $factory */
        $factory = $this->factory;
        /* @var ResolvedBlockTypeInterface $type */
        $type = $this->getMock('Sonatra\Bundle\BlockBundle\Block\ResolvedBlockTypeInterface');
        $builder = new BlockBuilder($name, null, $dispatcher, $factory);
        $builder->setType($type);

        return $builder;
    }

    protected function getBlock($name = 'name')
    {
        return $this->getBuilder($name)->getBlock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getDataMapper()
    {
        return $this->getMock('Sonatra\Bundle\BlockBundle\Block\DataMapperInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockBlock()
    {
        return $this->getMock('Sonatra\Bundle\BlockBundle\Block\Test\BlockInterface');
    }

    public function testGetSubscriber()
    {
        $listener = new ResizeBlockListener('text', array());

        $this->assertEquals(array(BlockEvents::PRE_SET_DATA => 'preSetData'),$listener->getSubscribedEvents());
    }

    public function testPreSetDataResizesBlock()
    {
        $this->block->add($this->getBlock('0'));
        $this->block->add($this->getBlock('1'));

        $this->factory->expects($this->at(0))
            ->method('createNamed')
            ->with(1, 'text', null, array('property_path' => '[1]', 'auto_initialize' => false))
            ->will($this->returnValue($this->getBlock('1')));
        $this->factory->expects($this->at(1))
            ->method('createNamed')
            ->with(2, 'text', null, array('property_path' => '[2]', 'auto_initialize' => false))
            ->will($this->returnValue($this->getBlock('2')));

        $data = array(1 => 'string', 2 => 'string');
        $event = new BlockEvent($this->block, $data);
        $listener = new ResizeBlockListener('text', array());
        $listener->preSetData($event);

        $this->assertFalse($this->block->has('0'));
        $this->assertTrue($this->block->has('1'));
        $this->assertTrue($this->block->has('2'));
    }

    /**
     * @expectedException \Sonatra\Bundle\BlockBundle\Block\Exception\UnexpectedTypeException
     */
    public function testPreSetDataRequiresArrayOrTraversable()
    {
        $data = 'no array or traversable';
        $event = new BlockEvent($this->block, $data);
        $listener = new ResizeBlockListener('text', array());
        $listener->preSetData($event);
    }

    public function testPreSetDataDealsWithNullData()
    {
        $this->factory->expects($this->never())->method('createNamed');

        $data = null;
        $event = new BlockEvent($this->block, $data);
        $listener = new ResizeBlockListener('text', array());
        $listener->preSetData($event);
    }
}
