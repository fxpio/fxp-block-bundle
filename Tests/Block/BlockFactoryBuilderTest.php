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

use Sonatra\Bundle\BlockBundle\Block\BlockExtensionInterface;
use Sonatra\Bundle\BlockBundle\Block\BlockFactoryBuilder;
use Sonatra\Bundle\BlockBundle\Block\BlockFactoryBuilderInterface;
use Sonatra\Bundle\BlockBundle\Block\BlockTypeExtensionInterface;
use Sonatra\Bundle\BlockBundle\Block\BlockTypeGuesserInterface;
use Sonatra\Bundle\BlockBundle\Block\BlockTypeInterface;
use Sonatra\Bundle\BlockBundle\Block\ResolvedBlockTypeFactoryInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class BlockFactoryBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BlockFactoryBuilderInterface
     */
    protected $builder;

    protected function setUp()
    {
        $this->builder = new BlockFactoryBuilder();
    }

    protected function tearDown()
    {
        $this->builder = null;
    }

    public function testSetResolvedBlockTypeFactory()
    {
        /* @var ResolvedBlockTypeFactoryInterface $typeFactory */
        $typeFactory = $this->getMockBuilder('Sonatra\Bundle\BlockBundle\Block\ResolvedBlockTypeFactoryInterface')->getMock();

        $builder = $this->builder->setResolvedTypeFactory($typeFactory);

        $this->assertInstanceOf('Sonatra\Bundle\BlockBundle\Block\BlockFactoryBuilderInterface', $builder);
    }

    public function testAddExtension()
    {
        /* @var BlockExtensionInterface $ext */
        $ext = $this->getMockBuilder('Sonatra\Bundle\BlockBundle\Block\BlockExtensionInterface')->getMock();

        $builder = $this->builder->addExtension($ext);

        $this->assertInstanceOf('Sonatra\Bundle\BlockBundle\Block\BlockFactoryBuilderInterface', $builder);
    }

    public function testAddExtensions()
    {
        $exts = array(
            $this->getMockBuilder('Sonatra\Bundle\BlockBundle\Block\BlockExtensionInterface')->getMock(),
        );

        $builder = $this->builder->addExtensions($exts);

        $this->assertInstanceOf('Sonatra\Bundle\BlockBundle\Block\BlockFactoryBuilderInterface', $builder);
    }

    public function testAddType()
    {
        /* @var BlockTypeInterface $type */
        $type = $this->getMockBuilder('Sonatra\Bundle\BlockBundle\Block\BlockTypeInterface')->getMock();

        $builder = $this->builder->addType($type);

        $this->assertInstanceOf('Sonatra\Bundle\BlockBundle\Block\BlockFactoryBuilderInterface', $builder);
    }

    public function testAddTypes()
    {
        $types = array(
            $this->getMockBuilder('Sonatra\Bundle\BlockBundle\Block\BlockTypeInterface')->getMock(),
        );

        $builder = $this->builder->addTypes($types);

        $this->assertInstanceOf('Sonatra\Bundle\BlockBundle\Block\BlockFactoryBuilderInterface', $builder);
    }

    public function testAddTypeExtension()
    {
        /* @var BlockTypeExtensionInterface $ext */
        $ext = $this->getMockBuilder('Sonatra\Bundle\BlockBundle\Block\BlockTypeExtensionInterface')->getMock();

        $builder = $this->builder->addTypeExtension($ext);

        $this->assertInstanceOf('Sonatra\Bundle\BlockBundle\Block\BlockFactoryBuilderInterface', $builder);
    }

    public function testAddTypeExtensions()
    {
        $exts = array(
            $this->getMockBuilder('Sonatra\Bundle\BlockBundle\Block\BlockTypeExtensionInterface')->getMock(),
        );

        $builder = $this->builder->addTypeExtensions($exts);

        $this->assertInstanceOf('Sonatra\Bundle\BlockBundle\Block\BlockFactoryBuilderInterface', $builder);
    }

    public function testAddTypeGuesser()
    {
        /* @var BlockTypeGuesserInterface $guesser */
        $guesser = $this->getMockBuilder('Sonatra\Bundle\BlockBundle\Block\BlockTypeGuesserInterface')->getMock();

        $builder = $this->builder->addTypeGuesser($guesser);

        $this->assertInstanceOf('Sonatra\Bundle\BlockBundle\Block\BlockFactoryBuilderInterface', $builder);
    }

    public function testAddTypeGuessers()
    {
        $guessers = array(
            $this->getMockBuilder('Sonatra\Bundle\BlockBundle\Block\BlockTypeGuesserInterface')->getMock(),
        );

        $builder = $this->builder->addTypeGuessers($guessers);

        $this->assertInstanceOf('Sonatra\Bundle\BlockBundle\Block\BlockFactoryBuilderInterface', $builder);
    }

    public function testGetBlockFactory()
    {
        /* @var BlockTypeInterface $type */
        $type = $this->getMockBuilder('Sonatra\Bundle\BlockBundle\Block\BlockTypeInterface')->getMock();
        $this->builder->addType($type);

        $of = $this->builder->getBlockFactory();

        $this->assertInstanceOf('Sonatra\Bundle\BlockBundle\Block\BlockFactory', $of);

        /* @var BlockTypeGuesserInterface $guesser */
        $guesser = $this->getMockBuilder('Sonatra\Bundle\BlockBundle\Block\BlockTypeGuesserInterface')->getMock();
        /* @var BlockTypeGuesserInterface $guesser2 */
        $guesser2 = $this->getMockBuilder('Sonatra\Bundle\BlockBundle\Block\BlockTypeGuesserInterface')->getMock();
        $this->builder->addTypeGuesser($guesser);
        $this->builder->addTypeGuesser($guesser2);

        $of = $this->builder->getBlockFactory();

        $this->assertInstanceOf('Sonatra\Bundle\BlockBundle\Block\BlockFactory', $of);
    }
}
