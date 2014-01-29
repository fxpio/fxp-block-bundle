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

use Sonatra\Bundle\BlockBundle\Twig\TokenParser\BlockThemeTokenParser;
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
     * @var \Twig_Environment
     */
    protected $environment;

    /**
     * Constructor.
     *
     * @param TwigRendererInterface $renderer
     */
    public function __construct(TwigRendererInterface $renderer)
    {
        $this->renderer = $renderer;
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
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'block_widget'      => new \Twig_Function_Node('Sonatra\Bundle\BlockBundle\Twig\Node\SearchAndRenderBlockNode', array('is_safe' => array('html'))),
            'block_component'   => new \Twig_Function_Node('Sonatra\Bundle\BlockBundle\Twig\Node\SearchAndRenderBlockNode', array('is_safe' => array('html'))),
            'block_label'       => new \Twig_Function_Node('Sonatra\Bundle\BlockBundle\Twig\Node\SearchAndRenderBlockNode', array('is_safe' => array('html'))),
            'block_row'         => new \Twig_Function_Node('Sonatra\Bundle\BlockBundle\Twig\Node\SearchAndRenderBlockNode', array('is_safe' => array('html'))),
            'block_javascript'  => new \Twig_Function_Node('Sonatra\Bundle\BlockBundle\Twig\Node\SearchAndRenderBlockNode', array('is_safe' => array('html'))),
            'block_stylesheet'  => new \Twig_Function_Node('Sonatra\Bundle\BlockBundle\Twig\Node\SearchAndRenderBlockNode', array('is_safe' => array('html'))),
            'block_twig_render' => new \Twig_Function_Method($this, 'renderBlock', array('is_safe' => array('html'))),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            'humanize' => new \Twig_Filter_Method($this, 'renderer->humanize'),
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
     * Render the block of twig resource.
     *
     * @param string $resource
     * @param string $blockName
     * @param array  $options
     *
     * @return string
     */
    public function renderBlock($resource, $blockName, array $options = array())
    {
        if (null !== $this->environment) {
            $template = $this->environment->loadTemplate($resource);

            return $template->renderBlock($blockName, $options);
        }

        return '';
    }
}
