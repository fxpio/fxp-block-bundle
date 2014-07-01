<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Block\Extension\Core\Type;

use Sonatra\Bundle\BlockBundle\Block\AbstractType;
use Sonatra\Bundle\BlockBundle\Block\BlockBuilderInterface;
use Sonatra\Bundle\BlockBundle\Block\BlockInterface;
use Sonatra\Bundle\BlockBundle\Block\BlockView;
use Sonatra\Bundle\BlockBundle\Block\Exception\LogicException;
use Sonatra\Bundle\BlockBundle\Block\Extension\Core\DataTransformer\ChoicesToValuesTransformer;
use Sonatra\Bundle\BlockBundle\Block\Extension\Core\DataTransformer\ChoiceToValueTransformer;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceListInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\Form\Extension\Core\ChoiceList\SimpleChoiceList;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ChoiceType extends AbstractType
{
    /**
     * Caches created choice lists.
     * @var array
     */
    private $choiceListCache = array();

    /**
     * {@inheritdoc}
     */
    public function buildBlock(BlockBuilderInterface $builder, array $options)
    {
        if (!$options['choice_list'] && !is_array($options['choices']) && !$options['choices'] instanceof \Traversable) {
            throw new LogicException('Either the option "choices" or "choice_list" must be set.');
        }

        if ($options['multiple']) {
            $builder->addViewTransformer(new ChoicesToValuesTransformer($options['choice_list']));

        } else {
            $builder->addViewTransformer(new ChoiceToValueTransformer($options['choice_list']));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(BlockView $view, BlockInterface $block, array $options)
    {
        /* @var ChoiceListInterface $choiceList */
        $choiceList = $options['choice_list'];

        $view->vars = array_replace($view->vars, array(
            'multiple'          => $options['multiple'],
            'expanded'          => $options['expanded'],
            'preferred_choices' => $choiceList->getPreferredViews(),
            'choices'           => $choiceList->getRemainingViews(),
            'choice_selections' => $choiceList->getIndicesForChoices((array) $view->vars['value']),
            'empty_value'       => $options['empty_value'],
            'inline'            => $options['inline'],
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $choiceListCache =& $this->choiceListCache;

        $choiceList = function (Options $options) use (&$choiceListCache) {
            // Harden against NULL values (like in EntityType and ModelType)
            $choices = null !== $options['choices'] ? $options['choices'] : array();

            // Reuse existing choice lists in order to increase performance
            $hash = md5(json_encode(array($choices, $options['preferred_choices'])));

            if (!isset($choiceListCache[$hash])) {
                $choiceListCache[$hash] = new SimpleChoiceList($choices, $options['preferred_choices']);
            }

            return $choiceListCache[$hash];
        };

        $compound = function (Options $options) {
            return $options['expanded'];
        };

        $resolver->setDefaults(array(
                'inline'            => true,
                'multiple'          => false,
                'expanded'          => false,
                'choice_list'       => $choiceList,
                'choices'           => array(),
                'preferred_choices' => array(),
                'empty_value'       => null,
                'compound'          => $compound,
                'data_class'        => null,
        ));

        $resolver->setAllowedTypes(array(
            'choice_list' => array('null', 'Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceListInterface'),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'field';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'choice';
    }
}
