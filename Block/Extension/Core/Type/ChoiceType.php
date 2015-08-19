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
use Symfony\Component\Form\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\ChoiceList\Factory\ChoiceListFactoryInterface;
use Symfony\Component\Form\ChoiceList\Factory\DefaultChoiceListFactory;
use Symfony\Component\Form\ChoiceList\Factory\PropertyAccessDecorator;
use Symfony\Component\Form\ChoiceList\View\ChoiceGroupView;
use Symfony\Component\Form\ChoiceList\View\ChoiceListView;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Options;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ChoiceType extends AbstractType
{
    /**
     * @var ChoiceListFactoryInterface
     */
    private $choiceListFactory;

    /**
     * Constructor.
     *
     * @param ChoiceListFactoryInterface|null $choiceListFactory
     */
    public function __construct(ChoiceListFactoryInterface $choiceListFactory = null)
    {
        $this->choiceListFactory = $choiceListFactory ?: new PropertyAccessDecorator(new DefaultChoiceListFactory());
    }

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
        /* @var ChoiceListView $choiceListView */
        $choiceListView = $block->getConfig()->hasAttribute('choice_list_view')
            ? $block->getConfig()->getAttribute('choice_list_view')
            : $this->createChoiceListView($options['choice_list'], $options);

        $view->vars = array_replace($view->vars, array(
            'multiple' => $options['multiple'],
            'expanded' => $options['expanded'],
            'selected_choices' => $this->getSelectedChoices($choiceListView->choices, (array) $view->vars['value']),
            'empty_value' => $options['empty_value'],
            'inline' => $options['inline'],
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $choiceListFactory = $this->choiceListFactory;

        $compound = function (Options $options) {
            return $options['expanded'];
        };

        $choiceTranslationDomainNormalizer = function (Options $options, $choiceTranslationDomain) {
            if (true === $choiceTranslationDomain) {
                return $options['translation_domain'];
            }

            return $choiceTranslationDomain;
        };

        $choiceListNormalizer = function (Options $options) use ($choiceListFactory) {
            if (null !== $options['choice_loader']) {
                return $choiceListFactory->createListFromLoader(
                    $options['choice_loader'],
                    $options['choice_value']
                );
            }

            // Harden against NULL values (like in EntityType and ModelType)
            $choices = null !== $options['choices'] ? $options['choices'] : array();

            // BC when choices are in the keys, not in the values
            if (!$options['choices_as_values']) {
                return $choiceListFactory->createListFromFlippedChoices($choices, $options['choice_value']);
            }

            return $choiceListFactory->createListFromChoices($choices, $options['choice_value']);
        };

        $resolver->setDefaults(array(
                'inline' => true,
                'multiple' => false,
                'expanded' => false,
                'choice_list' => null, // deprecated
                'choices' => array(),
                'choices_as_values' => false,
                'choice_loader' => null,
                'choice_label' => null,
                'choice_name' => null,
                'choice_value' => null,
                'choice_attr' => null,
                'preferred_choices' => array(),
                'group_by' => null,
                'empty_value' => null,
                'compound' => $compound,
                'data_class' => null,
                'block_name' => 'entry',
                'choice_translation_domain' => true,
        ));

        $resolver->setAllowedTypes('choice_list', array('null', 'Symfony\Component\Form\ChoiceList\ChoiceListInterface'));
        $resolver->setAllowedTypes('choices', array('null', 'array', '\Traversable'));
        $resolver->setAllowedTypes('choice_translation_domain', array('null', 'bool', 'string'));
        $resolver->setAllowedTypes('choices_as_values', 'bool');
        $resolver->setAllowedTypes('choice_loader', array('null', 'Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface'));
        $resolver->setAllowedTypes('choice_label', array('null', 'callable', 'string', 'Symfony\Component\PropertyAccess\PropertyPath'));
        $resolver->setAllowedTypes('choice_name', array('null', 'callable', 'string', 'Symfony\Component\PropertyAccess\PropertyPath'));
        $resolver->setAllowedTypes('choice_value', array('null', 'callable', 'string', 'Symfony\Component\PropertyAccess\PropertyPath'));
        $resolver->setAllowedTypes('choice_attr', array('null', 'array', 'callable', 'string', 'Symfony\Component\PropertyAccess\PropertyPath'));
        $resolver->setAllowedTypes('preferred_choices', array('array', '\Traversable', 'callable', 'string', 'Symfony\Component\PropertyAccess\PropertyPath'));
        $resolver->setAllowedTypes('group_by', array('null', 'array', '\Traversable', 'string', 'callable', 'string', 'Symfony\Component\PropertyAccess\PropertyPath'));

        $resolver->setNormalizer('choice_translation_domain', $choiceTranslationDomainNormalizer);
        $resolver->setNormalizer('choice_list', $choiceListNormalizer);
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

    /**
     * Get the selected choices.
     *
     * @param ChoiceGroupView[]|ChoiceView[] $choiceViews The choice views
     * @param string[]                       $values      The selected values
     *
     * @return ChoiceView[] The selected choices
     */
    protected function getSelectedChoices($choiceViews, array $values)
    {
        $selectedChoices = array();

        foreach ($choiceViews as $index => $choiceView) {
            if ($choiceView instanceof ChoiceGroupView) {
                $selectedChoices = array_merge($selectedChoices, $this->getSelectedChoices($choiceView->choices, $values));
            } elseif ($choiceView instanceof ChoiceView) {
                if (in_array($choiceView->value, $values)) {
                    $selectedChoices[] = $choiceView;
                }
            }
        }

        return $selectedChoices;
    }

    private function createChoiceListView(ChoiceListInterface $choiceList, array $options)
    {
        // If no explicit grouping information is given, use the structural
        // information from the "choices" option for creating groups
        if (!$options['group_by'] && $options['choices']) {
            $options['group_by'] = !$options['choices_as_values']
                ? self::flipRecursive($options['choices'])
                : $options['choices'];
        }

        return $this->choiceListFactory->createView(
            $choiceList,
            $options['preferred_choices'],
            $options['choice_label'],
            $options['choice_name'],
            $options['group_by'],
            $options['choice_attr']
        );
    }

    private static function flipRecursive($choices, &$output = array())
    {
        foreach ($choices as $key => $value) {
            if (is_array($value)) {
                $output[$key] = array();
                self::flipRecursive($value, $output[$key]);
                continue;
            }

            $output[$value] = $key;
        }

        return $output;
    }
}
