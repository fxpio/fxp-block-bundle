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

use Sonatra\Bundle\BlockBundle\Block\BlockConfigBuilder;
use Sonatra\Bundle\BlockBundle\Block\BlockConfigBuilderInterface;
use Sonatra\Bundle\BlockBundle\Block\DataMapperInterface;
use Sonatra\Bundle\BlockBundle\Block\DataTransformerInterface;
use Sonatra\Bundle\BlockBundle\Block\ResolvedBlockTypeInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class BlockConfigBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var BlockConfigBuilderInterface
     */
    protected $config;

    protected function setUp()
    {
        $this->dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        /* @var EventDispatcherInterface $dispatcher */
        $dispatcher = $this->dispatcher;
        $options = array(
            'foo' => 'bar',
        );

        $this->config = new BlockConfigBuilder('name', null, $dispatcher, $options);
    }

    protected function tearDown()
    {
        $this->dispatcher = null;
        $this->config = null;
    }

    public function testNotStringName()
    {
        $this->setExpectedException('Sonatra\Bundle\BlockBundle\Block\Exception\UnexpectedTypeException');

        new BlockConfigBuilder(array(), null, $this->dispatcher);
    }

    public function testInvalidName()
    {
        $this->setExpectedException('Sonatra\Bundle\BlockBundle\Block\Exception\InvalidArgumentException');

        new BlockConfigBuilder('foo@bar', null, $this->dispatcher);
    }

    public function testInvalidClassname()
    {
        $this->setExpectedException('Sonatra\Bundle\BlockBundle\Block\Exception\InvalidArgumentException');

        new BlockConfigBuilder('name', 'Foobar', $this->dispatcher);
    }

    public function testAddEvents()
    {
        /* @var EventSubscriberInterface $subscriber */
        $subscriber = $this->getMock('Symfony\Component\EventDispatcher\EventSubscriberInterface');

        $this->config->addEventListener('foo', function () {}, 0);
        $this->config->addEventSubscriber($subscriber);
    }

    public function testViewTransformers()
    {
        /* @var DataTransformerInterface $dataTransformer */
        $dataTransformer = $this->getMock('Sonatra\Bundle\BlockBundle\Block\DataTransformerInterface');
        /* @var DataTransformerInterface $dataTransformer2 */
        $dataTransformer2 = $this->getMock('Sonatra\Bundle\BlockBundle\Block\DataTransformerInterface');

        $this->config->addViewTransformer($dataTransformer);
        $this->config->addViewTransformer($dataTransformer2, true);
        $this->assertCount(2, $this->config->getViewTransformers());
        $this->assertSame($dataTransformer, $this->config->getViewTransformers()[1]);
        $this->assertSame($dataTransformer2, $this->config->getViewTransformers()[0]);

        $this->config->resetViewTransformers();
        $this->assertCount(0, $this->config->getViewTransformers());
    }

    public function testModelTransformers()
    {
        /* @var DataTransformerInterface $dataTransformer */
        $dataTransformer = $this->getMock('Sonatra\Bundle\BlockBundle\Block\DataTransformerInterface');
        /* @var DataTransformerInterface $dataTransformer2 */
        $dataTransformer2 = $this->getMock('Sonatra\Bundle\BlockBundle\Block\DataTransformerInterface');

        $this->config->addModelTransformer($dataTransformer);
        $this->config->addModelTransformer($dataTransformer2, true);
        $this->assertCount(2, $this->config->getModelTransformers());
        $this->assertSame($dataTransformer, $this->config->getModelTransformers()[0]);
        $this->assertSame($dataTransformer2, $this->config->getModelTransformers()[1]);

        $this->config->resetModelTransformers();
        $this->assertCount(0, $this->config->getModelTransformers());
    }

    public function testGettersAndSetters()
    {
        /* @var ResolvedBlockTypeInterface $type */
        $type = $this->getMock('Sonatra\Bundle\BlockBundle\Block\ResolvedBlockTypeInterface');
        /* @var DataMapperInterface $dataMapper */
        $dataMapper = $this->getMock('Sonatra\Bundle\BlockBundle\Block\DataMapperInterface');
        /* @var FormInterface $form */
        $form = $this->getMock('Symfony\Component\Form\FormInterface');

        $this->assertEquals('name', $this->config->getName());
        $this->assertSame($this->dispatcher, $this->config->getEventDispatcher());
        $this->assertEquals(array('foo' => 'bar'), $this->config->getOptions());
        $this->assertTrue($this->config->hasOption('foo'));
        $this->assertFalse($this->config->hasOption('baz'));
        $this->assertEquals('bar', $this->config->getOption('foo'));
        $this->assertEquals('bar', $this->config->getOption('baz', 'bar'));

        $this->assertSame($this->config, $this->config->setPropertyPath('field'));
        $this->assertInstanceOf('Symfony\Component\PropertyAccess\PropertyPath', $this->config->getPropertyPath());
        $this->assertSame($this->config, $this->config->setMapped(true));
        $this->assertTrue($this->config->getMapped());
        $this->assertSame($this->config, $this->config->setInheritData(true));
        $this->assertTrue($this->config->getInheritData());
        $this->assertSame($this->config, $this->config->setCompound(true));
        $this->assertTrue($this->config->getCompound());
        $this->assertSame($this->config, $this->config->setType($type));
        $this->assertSame($type, $this->config->getType());
        $this->assertSame($this->config, $this->config->setDataMapper($dataMapper));
        $this->assertSame($dataMapper, $this->config->getDataMapper());
        $this->assertSame($this->config, $this->config->setEmptyData('empty'));
        $this->assertEquals('empty', $this->config->getEmptyData());
        $this->assertSame($this->config, $this->config->setEmptyMessage('empty message'));
        $this->assertEquals('empty message', $this->config->getEmptyMessage());
        $this->assertFalse($this->config->hasAttribute('foo'));
        $this->assertSame($this->config, $this->config->setAttribute('foo', 'bar'));
        $this->assertTrue($this->config->hasAttribute('foo'));
        $this->assertEquals(array('foo' => 'bar'), $this->config->getAttributes());
        $this->assertSame($this->config, $this->config->setAttributes(array('bar' => 'foo')));
        $this->assertFalse($this->config->hasAttribute('foo'));
        $this->assertTrue($this->config->hasAttribute('bar'));
        $this->assertEquals(array('bar' => 'foo'), $this->config->getAttributes());
        $this->assertEquals('foo', $this->config->getAttribute('bar'));
        $this->assertEquals('bar', $this->config->getAttribute('foo', 'bar'));

        $this->assertSame($this->config, $this->config->setDataClass('Foobar'));
        $this->assertEquals('Foobar', $this->config->getDataClass());
        $this->assertSame($this->config, $this->config->setData('data'));
        $this->assertEquals('data', $this->config->getData());
        $this->assertSame($this->config, $this->config->setForm($form));
        $this->assertSame($form, $this->config->getForm());
        $this->assertSame($this->config, $this->config->setData('data'));
    }

    public function testAddEventListenerAfterGetBlockConfig()
    {
        $this->setExpectedException('Sonatra\Bundle\BlockBundle\Block\Exception\BadMethodCallException');

        $config = $this->getBlockConfig();
        $config->addEventListener('foo', function () {}, 0);
    }

    public function testAddEventSubscriberAfterGetBlockConfig()
    {
        $this->setExpectedException('Sonatra\Bundle\BlockBundle\Block\Exception\BadMethodCallException');

        /* @var EventSubscriberInterface $subscriber */
        $subscriber = $this->getMock('Symfony\Component\EventDispatcher\EventSubscriberInterface');

        $config = $this->getBlockConfig();
        $config->addEventSubscriber($subscriber);
    }

    public function testAddViewTransformersAfterGetBlockConfig()
    {
        $this->setExpectedException('Sonatra\Bundle\BlockBundle\Block\Exception\BadMethodCallException');

        /* @var DataTransformerInterface $dataTransformer */
        $dataTransformer = $this->getMock('Sonatra\Bundle\BlockBundle\Block\DataTransformerInterface');

        $config = $this->getBlockConfig();
        $config->addViewTransformer($dataTransformer);
    }

    public function testResetViewTransformersAfterGetBlockConfig()
    {
        $this->setExpectedException('Sonatra\Bundle\BlockBundle\Block\Exception\BadMethodCallException');

        $config = $this->getBlockConfig();
        $config->resetViewTransformers();
    }

    public function testAddModelTransformersAfterGetBlockConfig()
    {
        $this->setExpectedException('Sonatra\Bundle\BlockBundle\Block\Exception\BadMethodCallException');

        /* @var DataTransformerInterface $dataTransformer */
        $dataTransformer = $this->getMock('Sonatra\Bundle\BlockBundle\Block\DataTransformerInterface');
        $config = $this->getBlockConfig();
        $config->addModelTransformer($dataTransformer);
    }

    public function testResetModelTransformersAfterGetBlockConfig()
    {
        $this->setExpectedException('Sonatra\Bundle\BlockBundle\Block\Exception\BadMethodCallException');

        $config = $this->getBlockConfig();
        $config->resetModelTransformers();
    }

    public function testGetBlockConfigAfterGetBlockConfig()
    {
        $this->setExpectedException('Sonatra\Bundle\BlockBundle\Block\Exception\BadMethodCallException');

        $config = $this->getBlockConfig();
        $config->getBlockConfig();
    }

    public function testSetPropertyPathAfterGetBlockConfig()
    {
        $this->setExpectedException('Sonatra\Bundle\BlockBundle\Block\Exception\BadMethodCallException');

        $config = $this->getBlockConfig();
        $config->setPropertyPath('field');
    }

    public function testSetMappedAfterGetBlockConfig()
    {
        $this->setExpectedException('Sonatra\Bundle\BlockBundle\Block\Exception\BadMethodCallException');

        $config = $this->getBlockConfig();
        $config->setMapped(true);
    }

    public function testSetInheritDataAfterGetBlockConfig()
    {
        $this->setExpectedException('Sonatra\Bundle\BlockBundle\Block\Exception\BadMethodCallException');

        $config = $this->getBlockConfig();
        $config->setInheritData(true);
    }

    public function testSetCompoundAfterGetBlockConfig()
    {
        $this->setExpectedException('Sonatra\Bundle\BlockBundle\Block\Exception\BadMethodCallException');

        $config = $this->getBlockConfig();
        $config->setCompound(true);
    }

    public function testSetTypeAfterGetBlockConfig()
    {
        $this->setExpectedException('Sonatra\Bundle\BlockBundle\Block\Exception\BadMethodCallException');

        /* @var ResolvedBlockTypeInterface $type */
        $type = $this->getMock('Sonatra\Bundle\BlockBundle\Block\ResolvedBlockTypeInterface');

        $config = $this->getBlockConfig();
        $config->setType($type);
    }

    public function testSetDataMapperAfterGetBlockConfig()
    {
        $this->setExpectedException('Sonatra\Bundle\BlockBundle\Block\Exception\BadMethodCallException');

        /* @var DataMapperInterface $dataMapper */
        $dataMapper = $this->getMock('Sonatra\Bundle\BlockBundle\Block\DataMapperInterface');

        $config = $this->getBlockConfig();
        $config->setDataMapper($dataMapper);
    }

    public function testSetEmptyDataAfterGetBlockConfig()
    {
        $this->setExpectedException('Sonatra\Bundle\BlockBundle\Block\Exception\BadMethodCallException');

        $config = $this->getBlockConfig();
        $config->setEmptyData('empty');
    }

    public function testSetEmptyMessageAfterGetBlockConfig()
    {
        $this->setExpectedException('Sonatra\Bundle\BlockBundle\Block\Exception\BadMethodCallException');

        $config = $this->getBlockConfig();
        $config->setEmptyMessage('empty message');
    }

    public function testSetAttributeAfterGetBlockConfig()
    {
        $this->setExpectedException('Sonatra\Bundle\BlockBundle\Block\Exception\BadMethodCallException');

        $config = $this->getBlockConfig();
        $config->setAttribute('foo', 'bar');
    }

    public function testSetAttributesAfterGetBlockConfig()
    {
        $this->setExpectedException('Sonatra\Bundle\BlockBundle\Block\Exception\BadMethodCallException');

        $config = $this->getBlockConfig();
        $config->setAttributes(array('foo' => 'bar'));
    }

    public function testSetDataAfterGetBlockConfig()
    {
        $this->setExpectedException('Sonatra\Bundle\BlockBundle\Block\Exception\BadMethodCallException');

        $config = $this->getBlockConfig();
        $config->setData('data');
    }

    public function testSetDataClassAfterGetBlockConfig()
    {
        $this->setExpectedException('Sonatra\Bundle\BlockBundle\Block\Exception\BadMethodCallException');

        $config = $this->getBlockConfig();
        $config->setDataClass('Foobar');
    }

    public function testSetFormAfterGetBlockConfig()
    {
        $this->setExpectedException('Sonatra\Bundle\BlockBundle\Block\Exception\BadMethodCallException');

        /* @var DataMapperInterface $dataMapper */
        $form = $this->getMock('Symfony\Component\Form\FormInterface');

        $config = $this->getBlockConfig();
        $config->setForm($form);
    }

    /**
     * @return BlockConfigBuilderInterface
     */
    private function getBlockConfig()
    {
        return $this->config->getBlockConfig();
    }
}
