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

use Sonatra\Bundle\BlockBundle\Block\BlockFactory;
use Sonatra\Bundle\BlockBundle\Block\BlockFactoryInterface;
use Sonatra\Bundle\BlockBundle\Block\BlockRegistryInterface;
use Sonatra\Bundle\BlockBundle\Block\BlockTypeGuesserChain;
use Sonatra\Bundle\BlockBundle\Block\Guess\Guess;
use Sonatra\Bundle\BlockBundle\Block\Guess\TypeGuess;
use Sonatra\Bundle\BlockBundle\Block\ResolvedBlockTypeFactoryInterface;
use Sonatra\Bundle\BlockBundle\Tests\Block\Fixtures\Type\FooSubType;
use Sonatra\Bundle\BlockBundle\Tests\Block\Fixtures\Type\FooSubTypeWithParentInstance;
use Sonatra\Bundle\BlockBundle\Tests\Block\Fixtures\Type\FooType;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class BlockFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $guesser1;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $guesser2;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $registry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $resolvedTypeFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $builder;

    /**
     * @var BlockFactoryInterface
     */
    protected $factory;

    protected function setUp()
    {
        $this->resolvedTypeFactory = $this->getMock('Sonatra\Bundle\BlockBundle\Block\ResolvedBlockTypeFactoryInterface');
        $this->guesser1 = $this->getMock('Sonatra\Bundle\BlockBundle\Block\BlockTypeGuesserInterface');
        $this->guesser2 = $this->getMock('Sonatra\Bundle\BlockBundle\Block\BlockTypeGuesserInterface');
        $this->registry = $this->getMock('Sonatra\Bundle\BlockBundle\Block\BlockRegistryInterface');
        $this->builder = $this->getMock('Sonatra\Bundle\BlockBundle\Test\BlockBuilderInterface');

        /* @var BlockRegistryInterface $registry */
        $registry = $this->registry;
        /* @var ResolvedBlockTypeFactoryInterface $resolvedTypeFactory */
        $resolvedTypeFactory = $this->resolvedTypeFactory;

        $this->factory = new BlockFactory($registry, $resolvedTypeFactory);

        $this->registry->expects($this->any())
            ->method('getTypeGuesser')
            ->will($this->returnValue(new BlockTypeGuesserChain(array(
                $this->guesser1,
                $this->guesser2,
            ))));
    }

    protected function tearDown()
    {
        $this->factory = null;
    }

    public function testCreateNamedBuilderWithTypeName()
    {
        $options = array('a' => '1', 'b' => '2');
        $expectedOptions = array_merge($options, array('id' => 'name'));
        $resolvedOptions = array('a' => '2', 'b' => '3');
        $resolvedType = $this->getMockResolvedType();

        $this->registry->expects($this->once())
            ->method('getType')
            ->with('type')
            ->will($this->returnValue($resolvedType));

        $resolvedType->expects($this->once())
            ->method('createBuilder')
            ->with($this->factory, 'name', $expectedOptions)
            ->will($this->returnValue($this->builder));

        $this->builder->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue($resolvedOptions));

        $resolvedType->expects($this->once())
            ->method('buildBlock')
            ->with($this->builder, $resolvedOptions);

        $this->assertSame($this->builder, $this->factory->createNamedBuilder('name', 'type', null, $options));
    }

    public function testCreateNamedBuilderWithTypeInstance()
    {
        $options = array('a' => '1', 'b' => '2');
        $expectedOptions = array_merge($options, array('id' => 'name'));
        $resolvedOptions = array('a' => '2', 'b' => '3');
        $type = new FooType();
        $resolvedType = $this->getMockResolvedType();

        $this->resolvedTypeFactory->expects($this->once())
            ->method('createResolvedType')
            ->with($type)
            ->will($this->returnValue($resolvedType));

        $resolvedType->expects($this->once())
            ->method('createBuilder')
            ->with($this->factory, 'name', $expectedOptions)
            ->will($this->returnValue($this->builder));

        $this->builder->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue($resolvedOptions));

        $resolvedType->expects($this->once())
            ->method('buildBlock')
            ->with($this->builder, $resolvedOptions);

        $this->assertSame($this->builder, $this->factory->createNamedBuilder('name', $type, null, $options));
    }

    public function testCreateNamedBuilderWithTypeInstanceWithParentType()
    {
        $options = array('a' => '1', 'b' => '2');
        $expectedOptions = array_merge($options, array('id' => 'name'));
        $resolvedOptions = array('a' => '2', 'b' => '3');
        $type = new FooSubType();
        $resolvedType = $this->getMockResolvedType();
        $parentResolvedType = $this->getMockResolvedType();

        $this->registry->expects($this->once())
            ->method('getType')
            ->with('foo')
            ->will($this->returnValue($parentResolvedType));

        $this->resolvedTypeFactory->expects($this->once())
            ->method('createResolvedType')
            ->with($type, array(), $parentResolvedType)
            ->will($this->returnValue($resolvedType));

        $resolvedType->expects($this->once())
            ->method('createBuilder')
            ->with($this->factory, 'name', $expectedOptions)
            ->will($this->returnValue($this->builder));

        $this->builder->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue($resolvedOptions));

        $resolvedType->expects($this->once())
            ->method('buildBlock')
            ->with($this->builder, $resolvedOptions);

        $this->assertSame($this->builder, $this->factory->createNamedBuilder('name', $type, null, $options));
    }

    public function testCreateNamedBuilderWithTypeInstanceWithParentTypeInstance()
    {
        $options = array('a' => '1', 'b' => '2');
        $expectedOptions = array_merge($options, array('id' => 'name'));
        $resolvedOptions = array('a' => '2', 'b' => '3');
        $type = new FooSubTypeWithParentInstance();
        $resolvedType = $this->getMockResolvedType();
        $parentResolvedType = $this->getMockResolvedType();

        $this->resolvedTypeFactory->expects($this->at(0))
            ->method('createResolvedType')
            ->with($type->getParent())
            ->will($this->returnValue($parentResolvedType));

        $this->resolvedTypeFactory->expects($this->at(1))
            ->method('createResolvedType')
            ->with($type, array(), $parentResolvedType)
            ->will($this->returnValue($resolvedType));

        $resolvedType->expects($this->once())
            ->method('createBuilder')
            ->with($this->factory, 'name', $expectedOptions)
            ->will($this->returnValue($this->builder));

        $this->builder->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue($resolvedOptions));

        $resolvedType->expects($this->once())
            ->method('buildBlock')
            ->with($this->builder, $resolvedOptions);

        $this->assertSame($this->builder, $this->factory->createNamedBuilder('name', $type, null, $options));
    }

    public function testCreateNamedBuilderWithResolvedTypeInstance()
    {
        $options = array('a' => '1', 'b' => '2');
        $expectedOptions = array_merge($options, array('id' => 'name'));
        $resolvedOptions = array('a' => '2', 'b' => '3');
        $resolvedType = $this->getMockResolvedType();

        $resolvedType->expects($this->once())
            ->method('createBuilder')
            ->with($this->factory, 'name', $expectedOptions)
            ->will($this->returnValue($this->builder));

        $this->builder->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue($resolvedOptions));

        $resolvedType->expects($this->once())
            ->method('buildBlock')
            ->with($this->builder, $resolvedOptions);

        $this->assertSame($this->builder, $this->factory->createNamedBuilder('name', $resolvedType, null, $options));
    }

    public function testCreateNamedBuilderFillsDataOption()
    {
        $givenOptions = array('a' => '1', 'b' => '2');
        $expectedOptions = array_merge($givenOptions, array('data' => 'DATA', 'id' => 'name'));
        $resolvedOptions = array('a' => '2', 'b' => '3', 'data' => 'DATA');
        $resolvedType = $this->getMockResolvedType();

        $this->registry->expects($this->once())
            ->method('getType')
            ->with('type')
            ->will($this->returnValue($resolvedType));

        $resolvedType->expects($this->once())
            ->method('createBuilder')
            ->with($this->factory, 'name', $expectedOptions)
            ->will($this->returnValue($this->builder));

        $this->builder->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue($resolvedOptions));

        $resolvedType->expects($this->once())
            ->method('buildBlock')
            ->with($this->builder, $resolvedOptions);

        $this->assertSame($this->builder, $this->factory->createNamedBuilder('name', 'type', 'DATA', $givenOptions));
    }

    public function testCreateNamedBuilderDoesNotOverrideExistingDataOption()
    {
        $options = array('a' => '1', 'b' => '2', 'data' => 'CUSTOM');
        $expectedOptions = array_merge($options, array('id' => 'name'));
        $resolvedOptions = array('a' => '2', 'b' => '3', 'data' => 'CUSTOM');
        $resolvedType = $this->getMockResolvedType();

        $this->registry->expects($this->once())
            ->method('getType')
            ->with('type')
            ->will($this->returnValue($resolvedType));

        $resolvedType->expects($this->once())
            ->method('createBuilder')
            ->with($this->factory, 'name', $expectedOptions)
            ->will($this->returnValue($this->builder));

        $this->builder->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue($resolvedOptions));

        $resolvedType->expects($this->once())
            ->method('buildBlock')
            ->with($this->builder, $resolvedOptions);

        $this->assertSame($this->builder, $this->factory->createNamedBuilder('name', 'type', 'DATA', $options));
    }

    public function testCreateNamedBuilderThrowsUnderstandableException()
    {
        $this->setExpectedException('Sonatra\Bundle\BlockBundle\Block\Exception\UnexpectedTypeException', 'Expected argument of type "string, Sonatra\Bundle\BlockBundle\Block\ResolvedBlockTypeInterface or Sonatra\Bundle\BlockBundle\Block\BlockTypeInterface", "stdClass" given');

        $this->factory->createNamedBuilder('name', new \stdClass());
    }

    public function testCreateUsesTypeNameIfTypeGivenAsString()
    {
        $options = array('a' => '1', 'b' => '2');
        $resolvedOptions = array('a' => '2', 'b' => '3');
        $resolvedType = $this->getMockResolvedType();

        $this->registry->expects($this->once())
            ->method('getType')
            ->with('TYPE')
            ->will($this->returnValue($resolvedType));

        $resolvedType->expects($this->once())
            ->method('createBuilder')
            ->will($this->returnValue($this->builder));

        $this->builder->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue($resolvedOptions));

        $resolvedType->expects($this->once())
            ->method('buildBlock')
            ->with($this->builder, $resolvedOptions);

        $this->builder->expects($this->once())
            ->method('getBlock')
            ->will($this->returnValue('BLOCK'));

        $this->assertSame('BLOCK', $this->factory->create('TYPE', null, $options));
    }

    public function testCreateUsesTypeNameIfTypeGivenAsObject()
    {
        $options = array('a' => '1', 'b' => '2');
        $resolvedOptions = array('a' => '2', 'b' => '3');
        $resolvedType = $this->getMockResolvedType();

        $resolvedType->expects($this->once())
            ->method('createBuilder')
            ->will($this->returnValue($this->builder));

        $this->builder->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue($resolvedOptions));

        $resolvedType->expects($this->once())
            ->method('buildBlock')
            ->with($this->builder, $resolvedOptions);

        $this->builder->expects($this->once())
            ->method('getBlock')
            ->will($this->returnValue('BLOCK'));

        $this->assertSame('BLOCK', $this->factory->create($resolvedType, null, $options));
    }

    public function testCreateNamed()
    {
        $options = array('a' => '1', 'b' => '2');
        $expectedOptions = array_merge($options, array('id' => 'name'));
        $resolvedOptions = array('a' => '2', 'b' => '3');
        $resolvedType = $this->getMockResolvedType();

        $this->registry->expects($this->once())
            ->method('getType')
            ->with('type')
            ->will($this->returnValue($resolvedType));

        $resolvedType->expects($this->once())
            ->method('createBuilder')
            ->with($this->factory, 'name', $expectedOptions)
            ->will($this->returnValue($this->builder));

        $this->builder->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue($resolvedOptions));

        $resolvedType->expects($this->once())
            ->method('buildBlock')
            ->with($this->builder, $resolvedOptions);

        $this->builder->expects($this->once())
            ->method('getBlock')
            ->will($this->returnValue('BLOCK'));

        $this->assertSame('BLOCK', $this->factory->createNamed('name', 'type', null, $options));
    }

    public function testCreateBuilderForPropertyWithoutTypeGuesser()
    {
        $registry = $this->getMock('Sonatra\Bundle\BlockBundle\Block\BlockRegistryInterface');
        $factory = $this->getMockBuilder('Sonatra\Bundle\BlockBundle\Block\BlockFactory')
            ->setMethods(array('createNamedBuilder'))
            ->setConstructorArgs(array($registry, $this->resolvedTypeFactory))
            ->getMock();

        $factory->expects($this->once())
            ->method('createNamedBuilder')
            ->with('firstName', 'text', null, array())
            ->will($this->returnValue('builderInstance'));

        /* @var BlockFactoryInterface $factory */
        $this->builder = $factory->createBuilderForProperty('Application\Author', 'firstName');

        $this->assertEquals('builderInstance', $this->builder);
    }

    public function testCreateBuilderForPropertyCreatesBlockWithHighestConfidence()
    {
        $this->guesser1->expects($this->once())
            ->method('guessType')
            ->with('Application\Author', 'firstName')
            ->will($this->returnValue(new TypeGuess(
                        'text',
                        array('attr' => array('data-maxlength' => 10)),
                        Guess::MEDIUM_CONFIDENCE
                    )));

        $this->guesser2->expects($this->once())
            ->method('guessType')
            ->with('Application\Author', 'firstName')
            ->will($this->returnValue(new TypeGuess(
                        'password',
                        array('attr' => array('data-maxlength' => 7)),
                        Guess::HIGH_CONFIDENCE
                    )));

        $factory = $this->getMockFactory(array('createNamedBuilder'));

        $factory->expects($this->once())
            ->method('createNamedBuilder')
            ->with('firstName', 'password', null, array('attr' => array('data-maxlength' => 7)))
            ->will($this->returnValue('builderInstance'));

        /* @var BlockFactoryInterface $factory */
        $this->builder = $factory->createBuilderForProperty('Application\Author', 'firstName');

        $this->assertEquals('builderInstance', $this->builder);
    }

    public function testCreateBuilderCreatesTextBlockIfNoGuess()
    {
        $this->guesser1->expects($this->once())
            ->method('guessType')
            ->with('Application\Author', 'firstName')
            ->will($this->returnValue(null));

        $factory = $this->getMockFactory(array('createNamedBuilder'));

        $factory->expects($this->once())
            ->method('createNamedBuilder')
            ->with('firstName', 'text')
            ->will($this->returnValue('builderInstance'));

        /* @var BlockFactoryInterface $factory */
        $this->builder = $factory->createBuilderForProperty('Application\Author', 'firstName');

        $this->assertEquals('builderInstance', $this->builder);
    }

    public function testOptionsCanBeOverridden()
    {
        $this->guesser1->expects($this->once())
            ->method('guessType')
            ->with('Application\Author', 'firstName')
            ->will($this->returnValue(new TypeGuess(
                'text',
                array('attr' => array('data-maxlength' => 10)),
                Guess::MEDIUM_CONFIDENCE
            )));

        $factory = $this->getMockFactory(array('createNamedBuilder'));

        $factory->expects($this->once())
            ->method('createNamedBuilder')
            ->with('firstName', 'text', null, array('attr' => array('data-maxlength' => 11)))
            ->will($this->returnValue('builderInstance'));

        /* @var BlockFactoryInterface $factory */
        $this->builder = $factory->createBuilderForProperty(
            'Application\Author',
            'firstName',
            null,
            array('attr' => array('data-maxlength' => 11))
        );

        $this->assertEquals('builderInstance', $this->builder);
    }

    public function testCreateForPropertyWithoutTypeGuesser()
    {
        $expectedOptions = array('id' => 'firstName');
        $resolvedOptions = array('a' => '2', 'b' => '3');
        $resolvedType = $this->getMockResolvedType();

        $this->registry->expects($this->once())
            ->method('getType')
            ->with('text')
            ->will($this->returnValue($resolvedType));

        $resolvedType->expects($this->once())
            ->method('createBuilder')
            ->with($this->factory, 'firstName', $expectedOptions)
            ->will($this->returnValue($this->builder));

        $this->builder->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue($resolvedOptions));

        $resolvedType->expects($this->once())
            ->method('buildBlock')
            ->with($this->builder, $resolvedOptions);

        $this->builder->expects($this->once())
            ->method('getBlock')
            ->will($this->returnValue('BLOCK'));

        $this->assertSame('BLOCK', $this->factory->createForProperty('Application\Author', 'firstName'));
    }

    private function getMockResolvedType()
    {
        return $this->getMock('Sonatra\Bundle\BlockBundle\Block\ResolvedBlockTypeInterface');
    }

    private function getMockFactory(array $methods = array())
    {
        return $this->getMockBuilder('Sonatra\Bundle\BlockBundle\Block\BlockFactory')
            ->setMethods($methods)
            ->setConstructorArgs(array($this->registry, $this->resolvedTypeFactory))
            ->getMock();
    }
}
