<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\BlockBundle\Tests\DependencyInjection\Compiler;

use Fxp\Bundle\BlockBundle\DependencyInjection\Compiler\BlockPass;
use Fxp\Component\Block\Extension\Core\Type\BlockType;
use Fxp\Component\Block\Extension\DependencyInjection\DependencyInjectionExtension;
use Fxp\Component\Block\Guess\TypeGuess;
use Fxp\Component\Block\Tests\Fixtures\Extension\FooExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Tests case for Block compiler pass.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class BlockPassTest extends TestCase
{
    /**
     * @var string
     */
    protected $rootDir;

    /**
     * @var Filesystem
     */
    protected $fs;

    /**
     * @var BlockPass
     */
    protected $pass;

    protected function setUp()
    {
        $this->rootDir = sys_get_temp_dir().'/fxp_block-bundle_block_pass_tests';
        $this->fs = new Filesystem();
        $this->pass = new BlockPass();
    }

    protected function tearDown()
    {
        $this->fs->remove($this->rootDir);
        $this->pass = null;
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The service "test.block" must be public as block types are lazy-loaded.
     */
    public function testProcessBlockTypeWithNotPublicService()
    {
        $container = $this->getContainer();

        $this->assertTrue($container->hasDefinition('fxp_block.extension'));

        $blockDef = new Definition(BlockType::class);
        $blockDef->setTags(array('fxp_block.type' => array()));
        $blockDef->setPublic(false);
        $container->setDefinition('test.block', $blockDef);

        $this->pass->process($container);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The service "test.block_extension" must be public as block type extensions are lazy-loaded.
     */
    public function testProcessBlockTypeExtensionWithNotPublicService()
    {
        $container = $this->getContainer();

        $this->assertTrue($container->hasDefinition('fxp_block.extension'));

        $blockDef = new Definition(FooExtension::class);
        $blockDef->setTags(array('fxp_block.type_extension' => array()));
        $blockDef->setPublic(false);
        $container->setDefinition('test.block_extension', $blockDef);

        $this->pass->process($container);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Tagged block type extension must have the extended type configured using the extended_type/extended-type attribute, none was configured for the "test.block_extension" service.
     */
    public function testProcessBlockTypeExtensionWithoutExtendedType()
    {
        $container = $this->getContainer();

        $this->assertTrue($container->hasDefinition('fxp_block.extension'));

        $blockDef = new Definition(FooExtension::class);
        $blockDef->setTags(array('fxp_block.type_extension' => array()));
        $container->setDefinition('test.block_extension', $blockDef);

        $this->pass->process($container);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The service "test.block_guesser" must be public as block type guessers are lazy-loaded.
     */
    public function testProcessBlockTypeGuesserWithNotPublicService()
    {
        $container = $this->getContainer();

        $this->assertTrue($container->hasDefinition('fxp_block.extension'));

        $blockDef = new Definition(TypeGuess::class);
        $blockDef->setTags(array('fxp_block.type_guesser' => array()));
        $blockDef->setPublic(false);
        $container->setDefinition('test.block_guesser', $blockDef);

        $this->pass->process($container);
    }

    /**
     * Gets the container.
     *
     * @return ContainerBuilder
     */
    protected function getContainer()
    {
        $container = new ContainerBuilder(new ParameterBag(array(
            'kernel.cache_dir' => $this->rootDir.'/cache',
            'kernel.debug' => false,
            'kernel.environment' => 'test',
            'kernel.name' => 'kernel',
            'kernel.root_dir' => $this->rootDir,
            'kernel.project_dir' => $this->rootDir,
            'kernel.charset' => 'UTF-8',
            'assetic.debug' => false,
            'assetic.cache_dir' => $this->rootDir.'/cache/assetic',
            'kernel.bundles' => array(),
        )));

        $extDef = new Definition(DependencyInjectionExtension::class);
        $extDef->setProperty('container', new Reference('service_container'));
        $extDef->setArguments(array(
            array(),
            array(),
            array(),
        ));
        $container->setDefinition('fxp_block.extension', $extDef);

        return $container;
    }
}
