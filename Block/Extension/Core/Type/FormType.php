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
use Sonatra\Bundle\BlockBundle\Block\Util\BlockFormUtil;
use Symfony\Component\Form\FormConfigBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class FormType extends AbstractType
{
    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $formOptions;

    /**
     * Constructor.
     *
     * @param FormFactoryInterface $formFactory
     * @param string               $type
     */
    public function __construct(FormFactoryInterface $formFactory, $type = 'form')
    {
        $this->formFactory = $formFactory;
        $this->type = $type;
        $this->name = ('form' !== $type ? 'form_' : '').$type;
        $this->formOptions = array();
    }

    /**
     * {@inheritdoc}
     */
    public function buildBlock(BlockBuilderInterface $builder, array $options)
    {
        $name = isset($options['block_name']) ? $options['block_name'] : $builder->getName();
        $formOptions = array();

        foreach ($this->formOptions as $formOption) {
            $formOptions[$formOption] = $options[$formOption];
        }

        if (null !== $builder->getData()) {
            $formOptions['data'] = $builder->getData();
        }

        if (null !== $builder->getDataClass()) {
            $formOptions['data_class'] = $builder->getDataClass();
        }

        if (isset($formOptions['mapped'])) {
            $builder->setMapped($formOptions['mapped']);
        }

        if (isset($formOptions['inherit_data'])) {
            $builder->setInheritData($formOptions['inherit_data']);
        }

        $type = isset($options['form_type']) && null !== $options['form_type']
            ? $options['form_type']
            : $this->type;

        $builder->setForm($this->formFactory->createNamed($name, $type, null, $formOptions));
    }

    /**
     * {@inheritdoc}
     */
    public function addChild(BlockInterface $child, BlockInterface $block, array $options)
    {
        $parentForm = BlockFormUtil::getParentForm($block);
        $form = $child->getForm();

        if (null !== $parentForm && null !== $form) {
            /* @var FormConfigBuilderInterface $formConfig */
            $formConfig = $form->getConfig();

            if (!$parentForm->has($form->getName())) {
                $formConfig->setAutoInitialize(false);
                $parentForm->add($form);
            } else {
                $child->setForm($parentForm->get($form->getName()));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeChild(BlockInterface $child, BlockInterface $block, array $options)
    {
        $parentForm = BlockFormUtil::getParentForm($block);
        $form = $child->getForm();

        if (null !== $parentForm && null !== $form) {
            $parentForm->remove($form->getName());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(BlockView $view, BlockInterface $block, array $options)
    {
        if ('form' !== $this->type) {
            $pos = 0;

            if (isset($view->vars['block_prefixes'][0]) && 'block' === $view->vars['block_prefixes'][0]) {
                $pos = 1;
            }

            array_splice($view->vars['block_prefixes'], $pos, 0, 'form');
        }

        $view->vars = array_replace($view->vars, array(
            'block_form' => BlockFormUtil::createFormView($view, $block),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $options = $this->formFactory->createBuilder($this->type)->getOptions();

        unset($options['data_class']);
        unset($options['empty_data']);
        unset($options['error_bubbling']);

        $this->formOptions = array_keys($options);

        if ('form' === $this->type) {
            $options['form_type'] = null;
        }

        $resolver->setDefaults($options);

        if ('form' === $this->type) {
            $resolver->addAllowedTypes('form_type', array('null', 'string', 'Symfony\Component\Form\FormTypeInterface'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }
}
