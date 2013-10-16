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
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\OptionsResolver\Options;
use Sonatra\Bundle\BlockBundle\Block\Extension\Core\DataMapper\WrapperMapper;
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
     */
    public function __construct(FormFactoryInterface $formFactory, $type = 'form')
    {
        $this->formFactory = $formFactory;
        $this->type = $type;
        $this->name = ('form' !== $type ? 'form_' : '') . $type;
        $this->formOptions = array();
    }

    /**
     * {@inheritdoc}
     */
    public function buildBlock(BlockBuilderInterface $builder, array $options)
    {
        $name = isset($options['block_name']) ? $options['block_name'] : $builder->getName();
        $data = $builder->getData();
        $formOptions = array();

        $builder->setData(null);
        $builder->setDataClass(null);
        $builder->setDataMapper(new WrapperMapper());
        $builder->setInheritData(false);
        $builder->setEmptyData(null);
        $builder->setMapped(false);

        foreach ($this->formOptions as $formOption) {
            $formOptions[$formOption] = $options[$formOption];
        }

        $builder->setForm($this->formFactory->createNamed($name, $this->type, $data, $formOptions));
    }

    /**
     * {@inheritdoc}
     */
    public function addChild(BlockInterface $child, BlockInterface $block, array $options)
    {
        $parentForm = $this->getParentForm($block);
        $form = $child->getForm();

        if (null !== $parentForm && null !== $form) {
            $form->getConfig()->setAutoInitialize(false);
            $parentForm->add($form);
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
        $view->vars = array_replace($view->vars, array(
            'block_form' => $block->getForm()->createView(),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
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

        if (null !== $block->getParent()) {
            return $this->getParentForm($block->getParent());
        }

        return null;
    }
}
