<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Block;

use Sonatra\Bundle\BlockBundle\Block\Exception\LogicException;
use Sonatra\Bundle\BlockBundle\Block\Exception\InvalidArgumentException;
use Sonatra\Bundle\BlockBundle\Block\Exception\RuntimeException;
use Sonatra\Bundle\BlockBundle\Block\Exception\UnexpectedTypeException;
use Sonatra\Bundle\BlockBundle\Block\Util\BlockUtil;
use Sonatra\Bundle\BlockBundle\Block\Util\InheritDataAwareIterator;
use Symfony\Component\PropertyAccess\PropertyPath;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class Block implements \IteratorAggregate, BlockInterface
{
    /**
     * The block's configuration.
     * @var BlockConfigInterface
     */
    protected $config;

    /**
     * The parent of this block.
     * @var BlockInterface
     */
    protected $parent;

    /**
     * The children of this block.
     * @var array An array of BlockInterface instances
     */
    protected $children = array();

    /**
     * The block data in model format.
     * @var mixed
     */
    protected $modelData;

    /**
     * The block data in normalized format.
     * @var mixed
     */
    protected $normData;

    /**
     * The block data in view format.
     * @var mixed
     */
    protected $viewData;

    /**
     * Whether the block's data has been initialized.
     *
     * When the data is initialized with its default value, that default value
     * is passed through the transformer chain in order to synchronize the
     * model, normalized and block format for the first time. This is done
     * lazily in order to save performance when {@link setData()} is called
     * manually, making the initialization with the configured default value
     * superfluous.
     *
     * @var Boolean
     */
    protected $defaultDataSet = false;

    /**
     * Whether setData() is currently being called.
     * @var Boolean
     */
    protected $lockSetData = false;

    /**
     * Creates a new block based on the given configuration.
     *
     * @param BlockConfigInterface $config The block configuration.
     */
    public function __construct(BlockConfigInterface $config)
    {
        // Mapped blocks always need a data mapper, otherwise calls to
        // `setData` and `add` will not lead to the correct population of
        // the child blocks.
        if ($config->getMapped() && !$config->getDataMapper()) {
            throw new LogicException('Mapped blocks need a data mapper');
        }

        $this->config = $config;
    }

    public function __clone()
    {
        foreach ($this->children as $key => $child) {
            $this->children[$key] = clone $child;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->config->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function getPropertyPath()
    {
        if (null !== $this->config->getPropertyPath()) {
            return $this->config->getPropertyPath();
        }

        if (null === $this->getName() || '' === $this->getName()) {
            return null;
        }

        if ($this->hasParent() && null === $this->getParent()->getConfig()->getDataClass()) {
            return new PropertyPath('[' . $this->getName() . ']');
        }

        return new PropertyPath($this->getName());
    }

    /**
     * Sets the parent block.
     *
     * @param BlockInterface $parent The parent block
     *
     * @return Block The current block
     */
    public function setParent(BlockInterface $parent = null)
    {
        if (null !== $parent && '' === $this->config->getName()) {
            throw new LogicException('A block with an empty name cannot have a parent block.');
        }

        $this->parent = $parent;

        return $this;
    }

    /**
     * Returns the parent block.
     *
     * @return BlockInterface The parent block
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Returns whether the block has a parent.
     *
     * @return Boolean
     */
    public function hasParent()
    {
        return null !== $this->parent;
    }

    /**
     * Returns the root of the block tree.
     *
     * @return BlockInterface The root of the tree
     */
    public function getRoot()
    {
        return $this->parent ? $this->parent->getRoot() : $this;
    }

    /**
     * Returns whether the block is the root of the block tree.
     *
     * @return Boolean
     */
    public function isRoot()
    {
        return !$this->hasParent();
    }

    /**
     * Updates the block with default data.
     *
     * @param mixed $modelData The data formatted as expected for the underlying object
     *
     * @return Block The current block
     */
    public function setData($modelData)
    {
        if ($this->lockSetData) {
            throw new RuntimeException('A cycle was detected. Listeners to the PRE_SET_DATA event must not call setData(). You should call setData() on the BlockEvent object instead.');
        }

        $this->lockSetData = true;
        $dispatcher = $this->config->getEventDispatcher();

        // Hook to change content of the data
        if ($dispatcher->hasListeners(BlockEvents::PRE_SET_DATA)) {
            $event = new BlockEvent($this, $modelData);
            $dispatcher->dispatch(BlockEvents::PRE_SET_DATA, $event);
        }

        // Treat data as strings unless a value transformer exists
        if (!$this->config->getViewTransformers() && !$this->config->getModelTransformers() && is_scalar($modelData)) {
            $modelData = (string) $modelData;
        }

        // Synchronize representations - must not change the content!
        $normData = $this->modelToNorm($modelData);
        $viewData = $this->normToView($normData);

        // Validate if view data matches data class (unless empty)
        if (!BlockUtil::isEmpty($viewData)) {
            $dataClass = $this->config->getDataClass();

            $actualType = is_object($viewData) ? 'an instance of class ' . get_class($viewData) : ' a(n) ' . gettype($viewData);

            if (null === $dataClass && is_object($viewData) && !$viewData instanceof \ArrayAccess) {
                $expectedType = 'scalar, array or an instance of \ArrayAccess';

                throw new LogicException(
                        'The block\'s view data is expected to be of type ' . $expectedType . ', ' .
                        'but is ' . $actualType . '. You ' .
                        'can avoid this error by setting the "data_class" option to ' .
                        '"' . get_class($viewData) . '" or by adding a view transformer ' .
                        'that transforms ' . $actualType . ' to ' . $expectedType . '.'
                );
            }

            if (null !== $dataClass && !$viewData instanceof $dataClass) {
                throw new LogicException(
                        'The block\'s view data is expected to be an instance of class ' .
                        $dataClass . ', but is '. $actualType . '. You can avoid this error ' .
                        'by setting the "data_class" option to null or by adding a view ' .
                        'transformer that transforms ' . $actualType . ' to an instance of ' .
                        $dataClass . '.'
                );
            }
        }

        $this->modelData = $modelData;
        $this->normData = $normData;
        $this->viewData = $viewData;
        $this->defaultDataSet = true;
        $this->lockSetData = false;

        // It is not necessary to invoke this method if the block doesn't have children,
        // even if the block is compound.
        if (count($this->children) > 0) {
            // Update child blocks view the data
            $childrenIterator = new InheritDataAwareIterator($this->children);
            $childrenIterator = new \RecursiveIteratorIterator($childrenIterator);
            $this->config->getDataMapper()->mapDataToViews($viewData, $childrenIterator);
        }

        if ($dispatcher->hasListeners(BlockEvents::POST_SET_DATA)) {
            $event = new BlockEvent($this, $modelData);
            $dispatcher->dispatch(BlockEvents::POST_SET_DATA, $event);
        }

        return $this;
    }

    /**
     * Returns the data in the format needed for the underlying object.
     *
     * @return mixed
     */
    public function getData()
    {
        if (!$this->defaultDataSet) {
            $this->setData($this->config->getData());
        }

        return $this->modelData;
    }

    /**
     * Returns the normalized data of the block.
     *
     * @return mixed
     */
    public function getNormData()
    {
        if (!$this->defaultDataSet) {
            $this->setData($this->config->getData());
        }

        return $this->normData;
    }

    /**
     * Returns the data transformed by the value transformer.
     *
     * @return string
     */
    public function getViewData()
    {
        if (!$this->defaultDataSet) {
            $this->setData($this->config->getData());
        }

        return $this->viewData;
    }

    /**
     * Returns whether the block is empty.
     *
     * @return Boolean
     */
    public function isEmpty()
    {
        foreach ($this->children as $child) {
            if (!$child->isEmpty()) {
                return false;
            }
        }

        return BlockUtil::isEmpty($this->modelData) || array() === $this->modelData;
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return $this->children;
    }

    /**
     * {@inheritdoc}
     */
    public function add($child, $type = null, array $options = array())
    {
        if (!$this->config->getCompound()) {
            throw new LogicException('You cannot add children to a simple block. Maybe you should set the option "compound" to true?');
        }

        // Obtain the view data
        $viewData = null;

        // If setData() is currently being called, there is no need to call
        // mapDataToViews() here, as mapDataToViews() is called at the end
        // of setData() anyway. Not doing this check leads to an endless
        // recursion when initializing the block lazily and an event listener
        // (such as ResizeBlockListener) adds fields depending on the data:
        //
        //  * setData() is called, the block is not initialized yet
        //  * add() is called by the listener (setData() is not complete, so
        //    the block is still not initialized)
        //  * getViewData() is called
        //  * setData() is called since the block is not initialized yet
        //  * ... endless recursion ...
        if (!$this->lockSetData) {
            $viewData = $this->getViewData();
        }

        if (!$child instanceof BlockInterface) {
            if (null !== $child && !is_string($child) && !is_int($child)) {
                throw new UnexpectedTypeException($child, 'string, integer or Sonatra\Bundle\BlockBundle\Block\BlockInterface');
            }

            if (null !== $type && !is_string($type) && !$type instanceof BlockTypeInterface) {
                throw new UnexpectedTypeException($type, 'string or Sonatra\Bundle\BlockBundle\Block\BlockTypeInterface');
            }

            if (null === $type) {
                $child = $this->config->getBlockFactory()->createForProperty($this->config->getDataClass(), $child, null, $options);
            } elseif (null === $child) {
                $child = $this->config->getBlockFactory()->create($type, null, $options);
            } else {
                $child = $this->config->getBlockFactory()->createNamed($child, $type, null, $options);
            }
        }

        $this->getConfig()->getType()->validateChild($this->getConfig(), $child->getConfig());
        $this->children[$child->getName()] = $child;

        $child->setParent($this);

        if (!$this->lockSetData && $this->config->getMapped()) {
            $childrenIterator = new InheritDataAwareIterator(array($child));
            $childrenIterator = new \RecursiveIteratorIterator($childrenIterator);
            $this->config->getDataMapper()->mapDataToViews($viewData, $childrenIterator);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($name)
    {
        if (isset($this->children[$name])) {
            $this->children[$name]->setParent(null);

            unset($this->children[$name]);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        return isset($this->children[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        if (isset($this->children[$name])) {
            return $this->children[$name];
        }

        throw new InvalidArgumentException(sprintf('Child "%s" does not exist.', $name));
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($name)
    {
        return $this->has($name);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($name)
    {
        return $this->get($name);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($name, $child)
    {
        $this->add($child);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($name)
    {
        $this->remove($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->children);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->children);
    }

    /**
     * {@inheritdoc}
     */
    public function createView(BlockView $parent = null)
    {
        if (null === $parent && $this->parent) {
            $parent = $this->parent->createView();
        }

        $type = $this->config->getType();
        $options = $this->config->getOptions();

        // The methods createView(), buildView() and finishView() are called
        // explicitly here in order to be able to override either of them
        // in a custom resolved block type.
        $view = $type->createView($this, $parent);

        $type->buildView($view, $this, $options);

        foreach ($this->children as $name => $child) {
            $view->children[$name] = $child->createView($view);
        }

        $type->finishView($view, $this, $options);

        return $view;
    }

    /**
     * Normalizes the value if a normalization transformer is set.
     *
     * @param mixed $value The value to transform
     *
     * @return string
     */
    protected function modelToNorm($value)
    {
        foreach ($this->config->getModelTransformers() as $transformer) {
            $value = $transformer->transform($value);
        }

        return $value;
    }

    /**
     * Transforms the value if a value transformer is set.
     *
     * @param mixed $value The value to transform
     *
     * @return string
     */
    protected function normToView($value)
    {
        // Scalar values should  be converted to strings to
        // facilitate differentiation between empty ("") and zero (0).
        // Only do this for simple blocks, as the resulting value in
        // compound blocks is passed to the data mapper and thus should
        // not be converted to a string before.
        if (!$this->config->getViewTransformers() && !$this->config->getCompound()) {
            return null === $value || is_scalar($value) ? (string) $value : $value;
        }

        foreach ($this->config->getViewTransformers() as $transformer) {
            $value = $transformer->transform($value);
        }

        return $value;
    }
}
