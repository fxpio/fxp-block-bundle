<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Tests\DependencyInjection;

use Symfony\Bundle\FrameworkBundle\DependencyInjection\FrameworkExtension;
use Symfony\Bundle\TwigBundle\DependencyInjection\TwigExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Sonatra\Bundle\BlockBundle\SonatraBlockBundle;
use Sonatra\Bundle\BlockBundle\DependencyInjection\SonatraBlockExtension;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * Tests case for Extension.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SonatraBlockExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testExtensionExist()
    {
        $container = $this->createContainer();

        $this->assertTrue($container->hasExtension('sonatra_block'));
    }

    public function testExtensionLoader()
    {
        $container = $this->createContainer();

        // block
        $this->assertTrue($container->hasDefinition('sonatra_block.extension'));
        $this->assertTrue($container->hasDefinition('sonatra_block.factory'));
        $this->assertTrue($container->hasDefinition('sonatra_block.registry'));
        $this->assertTrue($container->hasDefinition('sonatra_block.resolved_type_factory'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type_guesser.validator'));

        $this->assertTrue($container->hasDefinition('sonatra_block.type.block'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.twig'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.output'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.object'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.field'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.text'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.textarea'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.password'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.email'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.url'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.checkbox'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.radio'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.datetime'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.date'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.time'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.birthday'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.timezone'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.number'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.integer'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.percent'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.money'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.currency'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.choice'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.country'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.locale'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.language'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.collection'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.repeated'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.hidden'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.closure'));

        // twig
        $this->assertTrue($container->hasDefinition('sonatra_block.twig.extension'));
        $this->assertTrue($container->hasDefinition('sonatra_block.twig.renderer'));
        $this->assertTrue($container->hasDefinition('sonatra_block.twig.engine'));

        // doctrine
        $this->assertTrue($container->hasDefinition('sonatra_block.type_guesser.doctrine'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.entity'));

        // debug
        $this->assertFalse($container->hasDefinition('sonatra_block.type_extension.block.data_collector'));

        //collector
        $this->assertFalse($container->hasDefinition('data_collector.sonatra_block.extractor'));
        $this->assertFalse($container->hasDefinition('data_collector.sonatra_block'));

        // parameter
        $this->assertTrue($container->hasParameter('sonatra_block.twig.resources'));

        // compiler block form
        $this->assertTrue($container->hasDefinition('form.extension'));

        $this->assertTrue($container->hasDefinition('sonatra_block.type.form'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.form_birthday'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.form_checkbox'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.form_choice'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.form_collection'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.form_country'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.form_date'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.form_datetime'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.form_email'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.form_file'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.form_hidden'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.form_integer'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.form_language'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.form_locale'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.form_money'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.form_number'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.form_password'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.form_percent'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.form_radio'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.form_repeated'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.form_search'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.form_textarea'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.form_text'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.form_time'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.form_timezone'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.form_url'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.form_button'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.form_submit'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.form_reset'));
        $this->assertTrue($container->hasDefinition('sonatra_block.type.form_currency'));
    }

    public function testExtensionLoaderWithDebugAndCollector()
    {
        $container = $this->createContainer(array(array(
            'profiler' => array(
                'enabled' => true,
                'collect' => true,
            ),
        )));

        $this->assertTrue($container->hasDefinition('sonatra_block.type_extension.block.data_collector'));

        //collector
        $this->assertTrue($container->hasDefinition('data_collector.sonatra_block.extractor'));
        $this->assertTrue($container->hasDefinition('data_collector.sonatra_block'));
    }

    public function testExtensionLoaderWithSeveralConfig()
    {
        $container = $this->createContainer(array(
                array(
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

        $this->assertCount(1, $container->getParameter('sonatra_block.twig.resources'));
    }

    public function testCompilerPassWithoutExtension()
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

        $sfExt = new FrameworkExtension();

        $container->registerExtension($sfExt);

        $sfExt->load(array(), $container);

        $bundle = new SonatraBlockBundle();
        $bundle->build($container);

        $container->getCompilerPassConfig()->setOptimizationPasses(array());
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->compile();
    }

    protected function createContainer(array $configs = array())
    {
        $container = new ContainerBuilder(new ParameterBag(array(
            'kernel.bundles'     => array(
                'FrameworkBundle'    => 'Symfony\\Bundle\\FrameworkBundle\\FrameworkBundle',
                'TwigBundle'         => 'Symfony\\Bundle\\TwigBundle\\TwigBundle',
                'SonatraBlockBundle' => 'Sonatra\\Bundle\\BlockBundle\\SonatraBlockBundle',
            ),
            'kernel.cache_dir'   => __DIR__,
            'kernel.debug'       => false,
            'kernel.environment' => 'test',
            'kernel.name'        => 'kernel',
            'kernel.root_dir'    => __DIR__,
            'kernel.charset'     => 'UTF-8',
        )));

        $sfExt = new FrameworkExtension();
        $twigExt = new TwigExtension();
        $extension = new SonatraBlockExtension();

        $container->registerExtension($sfExt);
        $container->registerExtension($twigExt);
        $container->registerExtension($extension);

        $sfExt->load(array(array('form' => true)), $container);
        $twigExt->load(array(), $container);
        $extension->load($configs, $container);

        if (!empty($twigConfigs)) {
            $container->prependExtensionConfig('twig', $twigConfigs[0]);
        }

        $bundle = new SonatraBlockBundle();
        $bundle->build($container);

        $container->getCompilerPassConfig()->setOptimizationPasses(array());
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->compile();

        return $container;
    }
}
