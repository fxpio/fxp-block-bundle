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
use Symfony\Component\Form\FormConfigBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

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

        $builder->setForm($this->formFactory->createNamed($name, $this->type, null, $formOptions));
    }

    /**
     * {@inheritdoc}
     */
    public function addChild(BlockInterface $child, BlockInterface $block, array $options)
    {
        $parentForm = $this->getParentForm($block);
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
        $parentForm = $this->getParentForm($block);
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
            'block_form' => $this->createFormView($view, $block),
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

        $resolver->setDefaults($options);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the parent form.
     *
     * @param BlockInterface $block
     *
     * @return FormInterface
     */
    protected function getParentForm(BlockInterface $block)
    {
        $form = $block->getForm();

        if (null !== $form) {
            return $form;
        }

        return null !== $block->getParent()
            ? $this->getParentForm($block->getParent())
            : null;
    }

    /**
     * Create form view.
     *
     * @param BlockView      $view
     * @param BlockInterface $block
     *
     * @return FormView
     */
    protected function createFormView(BlockView $view, BlockInterface $block)
    {
        /* @var FormView $parentForm */
        $parentForm = $this->getParentFormView($view);

        if (null !== $parentForm) {
            return $parentForm->vars['form']->children[$block->getName()];
        }

        return $block->getForm()->createView($parentForm);
    }

    /**
     * Get the parent form view.
     *
     * @param BlockView $view
     *
     * @return BlockView
     */
    protected function getParentFormView(BlockView $view)
    {
        if (isset($view->vars['block_form']) && null !== $view->vars['block_form']) {
            return $view->vars['block_form'];
        }

        return null !== $view->parent
            ? $this->getParentFormView($view->parent)
            : null;
    }
}
