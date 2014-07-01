<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Tests\Block\Extension\Core;

use Sonatra\Bundle\BlockBundle\Block\Extension\Core\CoreExtension;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class CoreExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CoreExtension
     */
    protected $extension;

    protected function setUp()
    {
        $this->extension = new CoreExtension();
    }

    protected function tearDown()
    {
        $this->extension = null;
    }

    public function testCoreExtension()
    {
        $this->assertInstanceOf('Sonatra\Bundle\BlockBundle\Block\BlockExtensionInterface', $this->extension);
        $this->assertFalse($this->extension->hasType('foo'));
        $this->assertFalse($this->extension->hasTypeExtensions('foo'));

        $this->assertTrue($this->extension->hasType('block'));
        $this->assertTrue($this->extension->hasType('birthday'));
        $this->assertTrue($this->extension->hasType('checkbox'));
        $this->assertTrue($this->extension->hasType('choice'));
        $this->assertTrue($this->extension->hasType('collection'));
        $this->assertTrue($this->extension->hasType('country'));
        $this->assertTrue($this->extension->hasType('date'));
        $this->assertTrue($this->extension->hasType('datetime'));
        $this->assertTrue($this->extension->hasType('email'));
        $this->assertTrue($this->extension->hasType('hidden'));
        $this->assertTrue($this->extension->hasType('integer'));
        $this->assertTrue($this->extension->hasType('language'));
        $this->assertTrue($this->extension->hasType('locale'));
        $this->assertTrue($this->extension->hasType('money'));
        $this->assertTrue($this->extension->hasType('number'));
        $this->assertTrue($this->extension->hasType('password'));
        $this->assertTrue($this->extension->hasType('percent'));
        $this->assertTrue($this->extension->hasType('radio'));
        $this->assertTrue($this->extension->hasType('repeated'));
        $this->assertTrue($this->extension->hasType('textarea'));
        $this->assertTrue($this->extension->hasType('text'));
        $this->assertTrue($this->extension->hasType('time'));
        $this->assertTrue($this->extension->hasType('timezone'));
        $this->assertTrue($this->extension->hasType('url'));
    }
}
