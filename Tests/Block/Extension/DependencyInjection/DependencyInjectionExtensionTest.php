<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Tests\Block\Extension\DependencyInjection;

use Sonatra\Bundle\BlockBundle\DependencyInjection\SonatraBlockExtension;
use Sonatra\Bundle\BlockBundle\SonatraBlockBundle;
use Sonatra\Bundle\BlockBundle\Tests\Block\AbstractBaseExtensionTest;
use Symfony\Bundle\FrameworkBundle\DependencyInjection\FrameworkExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class DependencyInjectionExtensionTest extends AbstractBaseExtensionTest
{
    protected function setUp()
    {
        $container = $this->getContainer('container1');

        $this->extension = $container->get('sonatra_block.extension');
    }

    protected function assertPreConditions()
    {
        $this->assertInstanceOf('Sonatra\Bundle\BlockBundle\Block\BlockExtensionInterface', $this->extension);
    }

    protected function getContainer($service)
    {
        $container = new ContainerBuilder(new ParameterBag(array(
            'kernel.bundles'     => array(
                'FrameworkBundle'    => 'Symfony\\Bundle\\FrameworkBundle\\FrameworkBundle',
                'SonatraBlockBundle' => 'Sonatra\\Bundle\\BlockBundle\\SonatraBlockBundle',
            ),
            'kernel.cache_dir'   => __DIR__,
            'kernel.debug'       => false,
            'kernel.environment' => 'test',
            'kernel.name'        => 'kernel',
            'kernel.root_dir'    => __DIR__,
            'kernel.charset'     => 'UTF-8',
        )));
        $bundle = new SonatraBlockBundle();
        $bundle->build($container); // Attach all default factories

        $sfExt = new FrameworkExtension();
        $container->registerExtension($sfExt);
        $sfExt->load(array(array('validation' => array('enabled' => true))), $container);

        $extension = new SonatraBlockExtension();
        $container->registerExtension($extension);
        $config = array('doctrine' => array('enabled' => false));
        $extension->load(array($config), $container);

        $load = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../Fixtures/config'));
        $load->load($service.'.xml');

        $container->getCompilerPassConfig()->setOptimizationPasses(array());
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->compile();

        return $container;
    }

    public function testInvalidServiceAlias()
    {
        $container = $this->getContainer('container2');
        $extension = $container->get('sonatra_block.extension');
        $this->assertInstanceOf('Sonatra\Bundle\BlockBundle\Block\BlockExtensionInterface', $extension);

        $this->setExpectedException('Sonatra\Bundle\BlockBundle\Block\Exception\InvalidArgumentException');
        $extension->getType('bar');
    }
}
