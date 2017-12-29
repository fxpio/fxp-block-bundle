<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\BlockBundle\Tests\Block\Extension\DependencyInjection;

use Fxp\Bundle\BlockBundle\DependencyInjection\FxpBlockExtension;
use Fxp\Bundle\BlockBundle\FxpBlockBundle;
use Fxp\Component\Block\Tests\AbstractBaseExtensionTest;
use Symfony\Bundle\FrameworkBundle\DependencyInjection\FrameworkExtension;
use Symfony\Bundle\TwigBundle\DependencyInjection\TwigExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class DependencyInjectionExtensionTest extends AbstractBaseExtensionTest
{
    protected function setUp()
    {
        $container = $this->getContainer('container1');

        $this->extension = $container->get('fxp_block.extension');
    }

    protected function assertPreConditions()
    {
        $this->assertInstanceOf('Fxp\Component\Block\BlockExtensionInterface', $this->extension);
    }

    protected function getContainer($service)
    {
        $container = new ContainerBuilder(new ParameterBag(array(
            'kernel.bundles' => array(
                'FrameworkBundle' => 'Symfony\\Bundle\\FrameworkBundle\\FrameworkBundle',
                'TwigBundle' => 'Symfony\\Bundle\\TwigBundle\\TwigBundle',
                'FxpBlockBundle' => 'Fxp\\Bundle\\BlockBundle\\FxpBlockBundle',
            ),
            'kernel.bundles_metadata' => array(),
            'kernel.cache_dir' => __DIR__,
            'kernel.debug' => false,
            'kernel.environment' => 'test',
            'kernel.name' => 'kernel',
            'kernel.root_dir' => __DIR__.'/Fixtures',
            'kernel.project_dir' => __DIR__,
            'kernel.charset' => 'UTF-8',
            'kernel.secret' => 'TestSecret',
        )));
        $bundle = new FxpBlockBundle();
        $bundle->build($container); // Attach all default factories

        $sfExt = new FrameworkExtension();
        $container->registerExtension($sfExt);
        $sfExt->load(array(array(
            'validation' => array('enabled' => true),
            'form' => array('enabled' => true),
            'templating' => array('engines' => array('twig')),
        )), $container);

        $twigExt = new TwigExtension();
        $container->registerExtension($twigExt);
        $twigExt->load(array(), $container);

        $extension = new FxpBlockExtension();
        $container->registerExtension($extension);
        $config = array('doctrine' => array('enabled' => false));
        $extension->load(array($config), $container);

        $load = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../Fixtures/config'));
        $load->load($service.'.xml');

        $container->findDefinition('validator.validator_factory')
            ->replaceArgument(0, ServiceLocatorTagPass::register($container, array()));

        $container->findDefinition('translator.default')
            ->replaceArgument(0, ServiceLocatorTagPass::register($container, array()));

        $container->findDefinition('fxp_block.extension')->setPublic(true);
        $container->findDefinition('fxp_block.type_guesser.validator')->setPublic(true);
        $container->findDefinition('test.fxp_block.type.foo')->setPublic(true);

        if ($container->hasDefinition('test.fxp_block.type_extension.foo')) {
            $container->findDefinition('test.fxp_block.type_extension.foo')->setPublic(true);
        }

        $container->getCompilerPassConfig()->setRemovingPasses(array());

        $container->compile();

        return $container;
    }

    /**
     * @expectedException \Fxp\Component\Block\Exception\InvalidArgumentException
     */
    public function testInvalidServiceAlias()
    {
        $container = $this->getContainer('container2');
        $extension = $container->get('fxp_block.extension');
        $this->assertInstanceOf('Fxp\Component\Block\BlockExtensionInterface', $extension);

        $extension->getType('bar');
    }
}
