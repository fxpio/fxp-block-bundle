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

use Sonatra\Bundle\BlockBundle\Block\BlockExtensionInterface;
use Sonatra\Bundle\BlockBundle\Tests\Block\Fixtures\TestExpectedExtension;
use Sonatra\Bundle\BlockBundle\Tests\Block\Fixtures\TestExtension;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AbstractExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Sonatra\Bundle\BlockBundle\Block\Exception\InvalidArgumentException
     */
    public function testGetUnexistingType()
    {
        /* @var BlockExtensionInterface $ext */
        $ext = $this->getMockForAbstractClass('Sonatra\Bundle\BlockBundle\Block\AbstractExtension');
        $ext->getType('unexisting_type');
    }

    /**
     * @expectedException \Sonatra\Bundle\BlockBundle\Block\Exception\UnexpectedTypeException
     */
    public function testInitLoadTypeException()
    {
        $ext = new TestExpectedExtension();
        $ext->getType('unexisting_type');
    }

    /**
     * @expectedException \Sonatra\Bundle\BlockBundle\Block\Exception\UnexpectedTypeException
     */
    public function testInitLoadTypeExtensionException()
    {
        $ext = new TestExpectedExtension();
        $ext->getTypeExtensions('unexisting_type');
    }

    /**
     * @expectedException \Sonatra\Bundle\BlockBundle\Block\Exception\UnexpectedTypeException
     */
    public function testInitLoadTypeGuesserException()
    {
        $ext = new TestExpectedExtension();
        $ext->getTypeGuesser();
    }

    public function testGetEmptyTypeExtension()
    {
        /* @var BlockExtensionInterface $ext */
        $ext = $this->getMockForAbstractClass('Sonatra\Bundle\BlockBundle\Block\AbstractExtension');
        $typeExts = $ext->getTypeExtensions('unexisting_type_extension');

        $this->assertTrue(is_array($typeExts));
        $this->assertCount(0, $typeExts);
    }

    public function testGetType()
    {
        $ext = new TestExtension();
        $type = $ext->getType('foo');

        $this->assertInstanceOf('Sonatra\Bundle\BlockBundle\Block\BlockTypeInterface', $type);
    }

    public function testHasType()
    {
        $ext = new TestExtension();

        $this->assertTrue($ext->hasType('foo'));
    }

    public function testGetTypeExtensions()
    {
        $ext = new TestExtension();
        $typeExts = $ext->getTypeExtensions('foo');

        $this->assertTrue(is_array($typeExts));
        $this->assertCount(1, $typeExts);
        $this->assertInstanceOf('Sonatra\Bundle\BlockBundle\Block\BlockTypeExtensionInterface', $typeExts[0]);
    }

    public function testHasTypeExtensions()
    {
        $ext = new TestExtension();

        $this->assertTrue($ext->hasTypeExtensions('foo'));
    }

    public function testGetTypeGuesser()
    {
        $ext = new TestExtension();
        $guesser = $ext->getTypeGuesser();

        $this->assertNull($guesser);
    }
}
