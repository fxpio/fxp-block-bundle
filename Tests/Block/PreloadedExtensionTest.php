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

use Sonatra\Bundle\BlockBundle\Block\BlockTypeGuesserInterface;
use Sonatra\Bundle\BlockBundle\Block\PreloadedExtension;
use Sonatra\Bundle\BlockBundle\Tests\Block\Fixtures\Extension\FooExtension;
use Sonatra\Bundle\BlockBundle\Tests\Block\Fixtures\Type\FooType;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PreloadedExtensionTest extends AbstractBaseExtensionTest
{
    protected function setUp()
    {
        $types = array(
            'foo' => new FooType(),
        );
        $extensions = array(
            'foo' => array(new FooExtension()),
        );
        /* @var BlockTypeGuesserInterface $guesser */
        $guesser = $this->getMock('Sonatra\Bundle\BlockBundle\Block\BlockTypeGuesserInterface');

        $this->extension = new PreloadedExtension($types, $extensions, $guesser);
    }
}
