<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Twig\Extension;

use Sonatra\Bundle\BlockBundle\Block\Block;
use Sonatra\Bundle\BlockBundle\Block\BlockFactoryInterface;
use Sonatra\Bundle\BlockBundle\Block\BlockView;
use Sonatra\Bundle\BlockBundle\Twig\TokenParser\BlockThemeTokenParser;
use Sonatra\Bundle\BlockBundle\Twig\TokenParser\SuperblockTokenParser;
use Sonatra\Bundle\BlockBundle\Twig\Block\TwigRendererInterface;

/**
 * BlockExtension extends Twig with block capabilities.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class BlockExtension extends \Twig_Extension
{
    /**
     * This property is public so that it can be accessed directly from compiled
     * templates without having to call a getter, which slightly decreases performance.
     * @var \Sonatra\Bundle\BlockBundle\Block\BlockRendererInterface
     */
    public $renderer;

    /**
     * @var BlockFactoryInterface
     */
    protected $factory;

    /**
     * @var \Twig_Environment
     */
    protected $environment;

    /**
     * @var array
     */
    protected $globalJavascripts;

    /**
     * @var array
     */
    protected $globalStylesheets;

    /**
     * Constructor.
     *
     * @param TwigRendererInterface $renderer
     */
    public function __construct(TwigRendererInterface $renderer, BlockFactoryInterface $factory)
    {
        $this->renderer = $renderer;
        $this->factory = $factory;
        $this->globalJavascripts = array();
        $this->globalStylesheets = array();
    }

    /**
     * {@inheritdoc}
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
        $this->renderer->setEnvironment($environment);
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenParsers()
    {
        return array(
            // {% block_theme form "SomeBundle::widgets.twig" %}
            new BlockThemeTokenParser(),
            // {% superblock 'checkbox', {data: true, label: "My checkbox" with {my_var: "the twig variable"} %}
            new SuperblockTokenParser(),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('block_widget',      'compile', array('node_class' => 'Sonatra\Bundle\BlockBundle\Twig\Node\SearchAndRenderBlockGlobalNode', 'is_safe' => array('html'))),
            new \Twig_SimpleFunction('block_component',   'compile', array('node_class' => 'Sonatra\Bundle\BlockBundle\Twig\Node\SearchAndRenderBlockNode', 'is_safe' => array('html'))),
            new \Twig_SimpleFunction('block_label',       'compile', array('node_class' => 'Sonatra\Bundle\BlockBundle\Twig\Node\SearchAndRenderBlockNode', 'is_safe' => array('html'))),
            new \Twig_SimpleFunction('block_row',         'compile', array('node_class' => 'Sonatra\Bundle\BlockBundle\Twig\Node\SearchAndRenderBlockNode', 'is_safe' => array('html'))),
            new \Twig_SimpleFunction('block_javascript',  'compile', array('node_class' => 'Sonatra\Bundle\BlockBundle\Twig\Node\SearchAndRenderBlockNode', 'is_safe' => array('html'))),
            new \Twig_SimpleFunction('block_stylesheet',  'compile', array('node_class' => 'Sonatra\Bundle\BlockBundle\Twig\Node\SearchAndRenderBlockNode', 'is_safe' => array('html'))),
            new \Twig_SimpleFunction('superblock',        array($this, 'createAndRenderSuperblock'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('block_twig_render', array($this, 'renderTwigBlock'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('block_global_javascripts', array($this, 'renderGlobalJavascripts'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('block_global_stylesheets', array($this, 'renderGlobalStylesheets'), array('is_safe' => array('html'))),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('block_humanize', array($this->renderer, 'humanize')),
            new \Twig_SimpleFilter('raw_closure', array($this, 'rawClosure')),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'sonatra_block';
    }

    /**
     * Create and render a superblock.
     *
     * @param string|BlockTypeInterface $type
     * @param array                     $options
     * @param array                     $variables    The twig variables
     * @param boolean                   $renderAssets
     *
     * @return string The html
     */
    public function createAndRenderSuperblock($type, array $options = array(), array $variables = array(), $renderAssets = true)
    {
        return $this->renderSuperblock($this->createBlock($name, $type, null, $options), $variables, $renderAssets);
    }

    /**
     * Create a superblock.
     *
     * @param string|BlockTypeInterface $type
     * @param array                     $options
     *
     * @return string The html
     */
    public function createBlock($type, array $options = array())
    {
        $name = isset($options['block_name']) ? $options['block_name'] : null;

        return $this->factory->createNamed($name, $type, null, $options);
    }

    /**
     * Render a superblock.
     *
     * @param Block   $block
     * @param array   $variables    The twig variables
     * @param boolean $renderAssets
     *
     * @return string The html
     */
    public function renderSuperblock(Block $block, array $variables = array(), $renderAssets = true)
    {
        $view = $block->createView();
        $output = $this->renderer->searchAndRenderBlock($view, 'widget', $variables);
        $this->addAssetBlock($view, $variables, $renderAssets);

        return $output;
    }

    /**
     * Render the block of twig resource.
     *
     * @param string $resource
     * @param string $blockName
     * @param array  $options
     *
     * @return string
     */
    public function renderTwigBlock($resource, $blockName, array $options = array())
    {
        if (null !== $this->environment) {
            $template = $this->environment->loadTemplate($resource);

            return $template->renderBlock($blockName, $options);
        }

        return '';
    }

    /**
     * Render global block twig of javascript.
     *
     * @return string
     */
    public function renderGlobalJavascripts()
    {
        $output = '';

        foreach ($this->globalJavascripts as $js) {
            $output .= $this->renderer->searchAndRenderBlock($js['view'], 'javascript', $js['variables']) . "\n";
        }

        $this->globalJavascripts = array();

        return $output;
    }

    /**
     * Render global block twig of javascript.
     *
     * @return string
     */
    public function renderGlobalStyleSheets()
    {
        $output = '';

        foreach ($this->globalStylesheets as $js) {
            $output .= $this->renderer->searchAndRenderBlock($js['view'], 'stylesheet', $js['variables']) . "\n";
        }

        $this->globalStylesheets = array();

        return $output;
    }

    /**
     * Add javascript and stylesheet of the block for to prepare the global
     * render of the blocks assets.
     *
     * @param BlockView $view
     * @param array     $variables
     * @param boolean   $renderAssets
     */
    public function addAssetBlock(BlockView $view, array $variables = array(), $renderAssets = true)
    {
        if (!$renderAssets || null !== $view->parent) {
            return;
        }

        $asset = array('view' => $view, 'variables' => $variables);
        $this->globalJavascripts[] = $asset;
        $this->globalStylesheets[] = $asset;
    }

    /**
     * Render closure value.
     *
     * @param string|\Closure $value
     *
     * @return string
     */
    public function rawClosure($value)
    {
        if ($value instanceof \Closure) {
            $value = $value();
        }

        return $value;
    }
}
