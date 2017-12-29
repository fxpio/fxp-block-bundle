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

use Fxp\Bundle\BlockBundle\DependencyInjection\FxpBlockExtension;
use Fxp\Bundle\BlockBundle\FxpBlockBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\DependencyInjection\FrameworkExtension;
use Symfony\Bundle\TwigBundle\DependencyInjection\TwigExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * Tests case for Extension.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class FxpBlockExtensionTest extends TestCase
{
    public function testExtensionExist()
    {
        $container = $this->createContainer();

        $this->assertTrue($container->hasExtension('fxp_block'));
    }

    public function testExtensionLoader()
    {
        $container = $this->createContainer();

        // block
        $this->assertTrue($container->hasDefinition('fxp_block.extension'));
        $this->assertTrue($container->hasDefinition('fxp_block.factory'));
        $this->assertTrue($container->hasDefinition('fxp_block.registry'));
        $this->assertTrue($container->hasDefinition('fxp_block.resolved_type_factory'));
        $this->assertTrue($container->hasDefinition('fxp_block.type_guesser.validator'));

        $this->assertTrue($container->hasDefinition('fxp_block.type.block'));
        $this->assertTrue($container->hasDefinition('fxp_block.type.twig'));
        $this->assertTrue($container->hasDefinition('fxp_block.type.output'));
        $this->assertTrue($container->hasDefinition('fxp_block.type.object'));
        $this->assertTrue($container->hasDefinition('fxp_block.type.field'));
        $this->assertTrue($container->hasDefinition('fxp_block.type.text'));
        $this->assertTrue($container->hasDefinition('fxp_block.type.textarea'));
        $this->assertTrue($container->hasDefinition('fxp_block.type.password'));
        $this->assertTrue($container->hasDefinition('fxp_block.type.email'));
        $this->assertTrue($container->hasDefinition('fxp_block.type.url'));
        $this->assertTrue($container->hasDefinition('fxp_block.type.checkbox'));
        $this->assertTrue($container->hasDefinition('fxp_block.type.radio'));
        $this->assertTrue($container->hasDefinition('fxp_block.type.datetime'));
        $this->assertTrue($container->hasDefinition('fxp_block.type.date'));
        $this->assertTrue($container->hasDefinition('fxp_block.type.time'));
        $this->assertTrue($container->hasDefinition('fxp_block.type.birthday'));
        $this->assertTrue($container->hasDefinition('fxp_block.type.timezone'));
        $this->assertTrue($container->hasDefinition('fxp_block.type.number'));
        $this->assertTrue($container->hasDefinition('fxp_block.type.integer'));
        $this->assertTrue($container->hasDefinition('fxp_block.type.percent'));
        $this->assertTrue($container->hasDefinition('fxp_block.type.money'));
        $this->assertTrue($container->hasDefinition('fxp_block.type.currency'));
        $this->assertTrue($container->hasDefinition('fxp_block.type.choice'));
        $this->assertTrue($container->hasDefinition('fxp_block.type.country'));
        $this->assertTrue($container->hasDefinition('fxp_block.type.locale'));
        $this->assertTrue($container->hasDefinition('fxp_block.type.language'));
        $this->assertTrue($container->hasDefinition('fxp_block.type.collection'));
        $this->assertTrue($container->hasDefinition('fxp_block.type.repeated'));
        $this->assertTrue($container->hasDefinition('fxp_block.type.hidden'));
        $this->assertTrue($container->hasDefinition('fxp_block.type.closure'));

        // twig
        $this->assertTrue($container->hasDefinition('fxp_block.twig.extension'));
        $this->assertTrue($container->hasDefinition('fxp_block.twig.renderer'));
        $this->assertTrue($container->hasDefinition('fxp_block.twig.engine'));

        // doctrine
        $this->assertTrue($container->hasDefinition('fxp_block.type_guesser.doctrine'));
        $this->assertTrue($container->hasDefinition('fxp_block.type.entity'));

        // debug
        $this->assertFalse($container->hasDefinition('fxp_block.type_extension.block.data_collector'));

        //collector
        $this->assertFalse($container->hasDefinition('data_collector.fxp_block.extractor'));
        $this->assertFalse($container->hasDefinition('data_collector.fxp_block'));

        // parameter
        $this->assertTrue($container->hasParameter('fxp_block.twig.resources'));

        // compiler block form
        $this->assertTrue($container->hasDefinition('form.extension'));

        $this->assertTrue($container->hasDefinition('fxp_block.type.form'));
    }

    public function testExtensionLoaderWithDebugAndCollector()
    {
        $container = $this->createContainer(array(array(
            'profiler' => array(
                'enabled' => true,
                'collect' => true,
            ),
        )));

        $this->assertTrue($container->hasDefinition('fxp_block.type_extension.block.data_collector'));

        //collector
        $this->assertTrue($container->hasDefinition('data_collector.fxp_block.extractor'));
        $this->assertTrue($container->hasDefinition('data_collector.fxp_block'));
    }

    public function testExtensionLoaderWithSeveralConfig()
    {
        $container = $this->createContainer(array(
                array(
                    'block_themes' => array(
                        'foobar.html.twig',
                    ),
                    'profiler' => array(
                        'enabled' => true,
                    ),
                ),
                array(
                    'profiler' => array(
                        'enabled' => false,
                    ),
                ),
            ));

        $validResources = array(
            'block_div_layout.html.twig',
            'foobar.html.twig',
        );

        $this->assertSame($validResources, $container->getParameter('fxp_block.twig.resources'));
    }

    public function testCompilerPassWithoutExtension()
    {
        $container = new ContainerBuilder(new ParameterBag(array(
            'kernel.bundles' => array(
                'FrameworkBundle' => 'Symfony\\Bundle\\FrameworkBundle\\FrameworkBundle',
                'FxpBlockBundle' => 'Fxp\\Bundle\\BlockBundle\\FxpBlockBundle',
            ),
            'kernel.bundles_metadata' => array(),
            'kernel.cache_dir' => __DIR__,
            'kernel.debug' => false,
            'kernel.environment' => 'test',
            'kernel.name' => 'kernel',
            'kernel.root_dir' => __DIR__,
            'kernel.project_dir' => __DIR__,
            'kernel.charset' => 'UTF-8',
        )));

        $sfExt = new FrameworkExtension();

        $container->registerExtension($sfExt);

        $sfExt->load(array(), $container);

        $bundle = new FxpBlockBundle();
        $bundle->build($container);

        $container->getCompilerPassConfig()->setOptimizationPasses(array());
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->compile();

        $this->assertFalse($container->hasDefinition('fxp_block.extension'));
    }

    protected function createContainer(array $configs = array())
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
            'kernel.root_dir' => __DIR__,
            'kernel.project_dir' => __DIR__,
            'kernel.charset' => 'UTF-8',
        )));

        $sfExt = new FrameworkExtension();
        $twigExt = new TwigExtension();
        $extension = new FxpBlockExtension();

        $container->registerExtension($sfExt);
        $container->registerExtension($twigExt);
        $container->registerExtension($extension);

        $sfExt->load(array(array('form' => true)), $container);
        $twigExt->load(array(), $container);
        $extension->load($configs, $container);

        if (!empty($twigConfigs)) {
            $container->prependExtensionConfig('twig', $twigConfigs[0]);
        }

        $bundle = new FxpBlockBundle();
        $bundle->build($container);

        $container->getCompilerPassConfig()->setOptimizationPasses(array());
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->compile();

        return $container;
    }
}
