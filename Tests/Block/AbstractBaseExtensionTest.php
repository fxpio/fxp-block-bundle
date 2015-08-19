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
use Sonatra\Bundle\BlockBundle\Block\BlockTypeExtensionInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class AbstractBaseExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BlockExtensionInterface
     */
    protected $extension;

    protected function setUp()
    {
        throw new \LogicException('The setUp() method must be overridden');
    }

    protected function tearDown()
    {
        $this->extension = null;
    }

    public function testHasType()
    {
        $this->assertTrue($this->extension->hasType('foo'));
        $this->assertFalse($this->extension->hasType('bar'));
    }

    public function testHasTypeExtension()
    {
        $this->assertTrue($this->extension->hasTypeExtensions('foo'));
        $this->assertFalse($this->extension->hasTypeExtensions('bar'));
    }

    public function testGetType()
    {
        $type = $this->extension->getType('foo');

        $this->assertInstanceOf('Sonatra\Bundle\BlockBundle\Block\BlockTypeInterface', $type);
        $this->assertEquals('foo', $type->getName());
    }

    public function testGetUnexistingType()
    {
        $this->setExpectedException('Sonatra\Bundle\BlockBundle\Block\Exception\InvalidArgumentException');
        $this->extension->getType('bar');
    }

    public function testGetTypeExtension()
    {
        $exts = $this->extension->getTypeExtensions('foo');

        $this->assertTrue(is_array($exts));
        $this->assertCount(1, $exts);

        /* @var BlockTypeExtensionInterface $ext */
        $ext = $exts[0];
        $this->assertInstanceOf('Sonatra\Bundle\BlockBundle\Block\BlockTypeExtensionInterface', $ext);
        $this->assertEquals('foo', $ext->getExtendedType());
    }

    public function testGetGuess()
    {
        $guesser = $this->extension->getTypeGuesser();

        $this->assertInstanceOf('Sonatra\Bundle\BlockBundle\Block\BlockTypeGuesserInterface', $guesser);
    }
}
