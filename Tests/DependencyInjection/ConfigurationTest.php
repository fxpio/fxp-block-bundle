<?php

/**
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Tests\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Sonatra\Bundle\BlockBundle\DependencyInjection\Configuration;

/**
 * Tests case for Configuration.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultConfig()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), array(array()));

        $this->assertEquals(
                array_merge(array(), self::getBundleDefaultConfig()),
                $config
        );
    }

    public function testCustomConfig()
    {
        $configs = array(array(
            'block' => array(
                'resources' => array(
                    'foobar.html.twig',
                ),
            ),
        ));

        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), $configs);

        $this->assertEquals(
            array_merge(array(), self::getBundleCustomConfig()),
            $config
        );
    }

    protected static function getBundleDefaultConfig()
    {
        return array(
            'block' => array(
                'resources' => array(
                    'block_div_layout.html.twig',
                ),
            ),
            'doctrine' => array(
                'enabled' => true,
            ),
            'profiler' => array(
                'enabled' => false,
                'collect' => true,
            ),
        );
    }

    protected static function getBundleCustomConfig()
    {
        return array(
            'block' => array(
                'resources' => array(
                    'block_div_layout.html.twig',
                    'foobar.html.twig',
                ),
            ),
            'doctrine' => array(
                'enabled' => true,
            ),
            'profiler' => array(
                'enabled' => false,
                'collect' => true,
            ),
        );
    }
}
