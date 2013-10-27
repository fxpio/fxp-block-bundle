<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Doctrine\Block\Type;

use Sonatra\Bundle\BlockBundle\Block\AbstractType;
use Sonatra\Bundle\BlockBundle\Block\BlockBuilderInterface;
use Sonatra\Bundle\BlockBundle\Block\BlockInterface;
use Sonatra\Bundle\BlockBundle\Block\BlockView;
use Sonatra\Bundle\BlockBundle\Doctrine\Block\ChoiceList\EntityChoiceList;
use Sonatra\Bundle\BlockBundle\Doctrine\Block\DataTransformer\CollectionToArrayTransformer;
use Sonatra\Bundle\BlockBundle\Doctrine\Block\DataTransformer\EntityToArrayTransformer;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class EntityType extends AbstractType
{
    /**
     * @var RegistryInterface
     */
    protected $registry;

    /**
     * Constructor.
     *
     * @param RegistryInterface $registry The doctrine instance
     */
    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function buildBlock(BlockBuilderInterface $builder, array $options)
    {
        $builder->resetViewTransformers();

        if ($options['multiple']) {
            $builder->addViewTransformer(new CollectionToArrayTransformer(), true);
        } else {
            $builder->addViewTransformer(new EntityToArrayTransformer(), true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(BlockView $view, BlockInterface $block, array $options)
    {
        $view->vars = array_replace($view->vars, array(
                'choice_selections' => $options['choice_list']->getIndicesForChoices((array) $view->vars['value']),
                'choices'           => $options['choice_list']->getRemainingViews(),
                'route_name'        => $options['route_name'],
                'route_parameters'  => array_merge($options['route_parameters'], array($options['route_id_name'] => null)),
                'route_id_name'     => $options['route_id_name'],

        ));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $choiceList = function (Options $options) {
            return new EntityChoiceList($this->registry, $options['property']);
        };

        $resolver->setDefaults(array(
                'property'         => null,
                'choice_list'      => $choiceList,
                'route_name'       => null,
                'route_parameters' => array(),
                'route_id_name'    => 'id',
        ));

        $resolver->setRequired(array('class'));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'choice';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'entity';
    }
}
