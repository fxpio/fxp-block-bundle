<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\BlockBundle\Tests\DependencyInjection;

use Fxp\Bundle\BlockBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

/**
 * Tests case for Configuration.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ConfigurationTest extends TestCase
{
    public function testDefaultConfig()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [[]]);

        $this->assertEquals(
                array_merge([], self::getBundleDefaultConfig()),
                $config
        );
    }

    public function testCustomConfig()
    {
        $configs = [[
            'block_themes' => [
                'foobar.html.twig',
            ],
        ]];

        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), $configs);

        $this->assertEquals(
            array_merge([], self::getBundleCustomConfig()),
            $config
        );
    }

    protected static function getBundleDefaultConfig()
    {
        return [
            'block_themes' => [],
            'doctrine' => [
                'enabled' => true,
            ],
            'profiler' => [
                'enabled' => false,
                'collect' => true,
            ],
        ];
    }

    protected static function getBundleCustomConfig()
    {
        return [
            'block_themes' => [
                'foobar.html.twig',
            ],
            'doctrine' => [
                'enabled' => true,
            ],
            'profiler' => [
                'enabled' => false,
                'collect' => true,
            ],
        ];
    }
}
