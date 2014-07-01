<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Tests\Block\Exception;

use Sonatra\Bundle\BlockBundle\Block\Exception\InvalidChildException;
use Sonatra\Bundle\BlockBundle\Test\BlockBuilderInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class InvalidChildExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BlockBuilderInterface
     */
    protected $builder;

    /**
     * @var BlockBuilderInterface
     */
    protected $builderChild;

    protected function setUp()
    {
        $type = $this->getMock('Sonatra\Bundle\BlockBundle\Block\ResolvedBlockTypeInterface');
        $type->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('custom_type'));

        $this->builder = $this->getMock('Sonatra\Bundle\BlockBundle\Block\BlockBuilderInterface');
        $this->builder->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('foo'));
        $this->builder->expects($this->any())
            ->method('getType')
            ->will($this->returnValue($type));

        $this->builderChild = $this->getMock('Sonatra\Bundle\BlockBundle\Block\BlockBuilderInterface');
        $this->builderChild->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('bar'));
        $this->builderChild->expects($this->any())
            ->method('getType')
            ->will($this->returnValue($type));
    }

    protected function tearDown()
    {
        $this->builder = null;
        $this->builderChild = null;
    }

    /**
     * @expectedException \Sonatra\Bundle\BlockBundle\Block\Exception\InvalidChildException
     * @expectedExceptionMessage The child "bar" ("custom_type" type) is not allowed for "foo" block ("custom_type" type)
     */
    public function testExceptionWithoutAllowedType()
    {
        throw new InvalidChildException($this->builder, $this->builderChild);
    }

    /**
     * @expectedException \Sonatra\Bundle\BlockBundle\Block\Exception\InvalidChildException
     * @expectedExceptionMessage The child "bar" ("custom_type" type) is not allowed for "foo" block ("custom_type" type), only "baz" allowed
     */
    public function testExceptionWithSingleAllowedType()
    {
        throw new InvalidChildException($this->builder, $this->builderChild, 'baz');
    }

    /**
     * @expectedException \Sonatra\Bundle\BlockBundle\Block\Exception\InvalidChildException
     * @expectedExceptionMessage The child "bar" ("custom_type" type) is not allowed for "foo" block ("custom_type" type), only "baz", "boo" allowed
     */
    public function testExceptionWithMultipleAllowedType()
    {
        throw new InvalidChildException($this->builder, $this->builderChild, array('baz', 'boo'));
    }
}
