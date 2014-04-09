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
use Sonatra\Bundle\BlockBundle\Block\Util\OrderedHashMap;
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
     * The options of this block.
     * @var array
     */
    protected $options = array();

    /**
     * The attributes of this block.
     * @var array
     */
    protected $attributes = array();

    /**
     * The children of this block.
     * @var BlockInterface[] A map of BlockInterface instances
     */
    protected $children;

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
     * @var string
     */
    protected $dataClass;

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
        // Compound blocks always need a data mapper, otherwise calls to
        // `setData` and `add` will not lead to the correct population of
        // the child blocks.
        if ($config->getCompound() && !$config->getDataMapper()) {
            throw new LogicException('Compound blocks need a data mapper');
        }

        // If the block inherits the data from its parent, it is not necessary
        // to call setData() with the default data.
        if ($config->getInheritData()) {
            $this->defaultDataSet = true;
        }

        $this->config = $config;
        $this->children = new OrderedHashMap();
        $this->options = $config->getOptions();
        $this->attributes = $config->getAttributes();
    }

    public function __clone()
    {
        $this->children = clone $this->children;

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

        $parent = $this->parent;

        while ($parent && $parent->getConfig()->getInheritData()) {
            $parent = $parent->getParent();
        }

        if ($parent && null === $parent->getConfig()->getDataClass()) {
            return new PropertyPath('['.$this->getName().']');
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
     * Sets the value for an option.
     *
     * @param string $name  The name of the option
     * @param string $value The value of the option
     *
     * @return BlockInterface The block instance
     */
    public function setOption($name, $value)
    {
        if (!$this->hasOption($name)) {
            throw new InvalidArgumentException(sprintf('The option "%s" does not exist', $name));
        }

        $this->options[$name] = $value;

        return $this;
    }

    /**
     * Sets the options.
     *
     * @param array $options The options.
     *
     * @return BlockInterface The block instance
     */
    public function setOptions(array $options)
    {
        foreach ($options as $name => $value) {
            $this->setOption($name, $value);
        }

        return $this;
    }

    /**
     * Returns all options of this Block instance.
     *
     * @return array The passed options.
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Returns whether a specific option exists.
     *
     * @param string $name The option name,
     *
     * @return Boolean Whether the option exists.
     */
    public function hasOption($name)
    {
        return array_key_exists($name, $this->options);
    }

    /**
     * Returns the value of a specific option.
     *
     * @param string $name    The option name.
     * @param mixed  $default The value returned if the option does not exist.
     *
     * @return mixed The option value.
     */
    public function getOption($name, $default = null)
    {
        return $this->hasOption($name) ? $this->options[$name] : $default;
    }

    /**
     * Sets the value for an attribute.
     *
     * @param string $name  The name of the attribute
     * @param string $value The value of the attribute
     *
     * @return BlockInterface The block instance
     */
    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * Sets the attributes.
     *
     * @param array $attributes The attributes.
     *
     * @return BlockInterface The block instance
     */
    public function setAttributes(array $attributes)
    {
        foreach ($attributes as $name => $value) {
            $this->setAttribute($name, $value);
        }

        return $this;
    }

    /**
     * Returns all attributes of this Block instance.
     *
     * @return array The passed attributes.
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Returns whether a specific attribute exists.
     *
     * @param string $name The attribute name,
     *
     * @return Boolean Whether the attribute exists.
     */
    public function hasAttribute($name)
    {
        return array_key_exists($name, $this->attributes);
    }

    /**
     * Returns the value of a specific attribute.
     *
     * @param string $name    The attribute name.
     * @param mixed  $default The value returned if the attribute does not exist.
     *
     * @return mixed The attribute value.
     */
    public function getAttribute($name, $default = null)
    {
        return $this->hasAttribute($name) ? $this->attributes[$name] : $default;
    }

    /**
     * Returns the form.
     *
     * @return Form|null
     */
    public function getForm()
    {
        return $this->config->getForm();
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
        // If the block inherits its parent's data, disallow data setting to
        // prevent merge conflicts
        if ($this->config->getInheritData()) {
            throw new RuntimeException('You cannot change the data of a block inheriting its parent data.');
        }

        if ($this->lockSetData) {
            throw new RuntimeException('A cycle was detected. Listeners to the PRE_SET_DATA event must not call setData(). You should call setData() on the BlockEvent object instead.');
        }

        if (null !== $this->getForm()) {
            $this->getForm()->setData($modelData);
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
            $dataClass = $this->getDataClass();

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

        } else {
            $viewData = $this->getConfig()->getEmptyData();

            if ($viewData instanceof \Closure) {
                $viewData = call_user_func($viewData, $this, $this->getOptions());
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
        if ($this->config->getInheritData()) {
            if (!$this->parent) {
                throw new RuntimeException('The block is configured to inherit its parent\'s data, but does not have a parent.');
            }

            return $this->parent->getData();
        }

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
        if ($this->config->getInheritData()) {
            if (!$this->parent) {
                throw new RuntimeException('The block is configured to inherit its parent\'s data, but does not have a parent.');
            }

            return $this->parent->getNormData();
        }

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
        if ($this->config->getInheritData()) {
            if (!$this->parent) {
                throw new RuntimeException('The block is configured to inherit its parent\'s data, but does not have a parent.');
            }

            return $this->parent->getViewData();
        }

        if (!$this->defaultDataSet) {
            $this->setData($this->config->getData());
        }

        return $this->viewData;
    }

    /**
     * Sets the data class of the block.
     *
     * @param string $dataClass The data class of the block in application format.
     *
     * @return BlockInterface The child block
     */
    public function setDataClass($dataClass)
    {
        $this->dataClass = $dataClass;

        return $this;
    }

    /**
     * Returns the class of the block data or null if the data is scalar or an array.
     *
     * @return string The data class or null.
     */
    public function getDataClass()
    {
        if (null === $this->dataClass) {
            return $this->config->getDataClass();
        }

        return $this->dataClass;
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
        if (!$this->lockSetData && $this->defaultDataSet && !$this->config->getInheritData()) {
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
                $child = $this->config->getBlockFactory()->createForProperty($this->getDataClass(), $child, null, $options);
            } elseif (null === $child) {
                $child = $this->config->getBlockFactory()->create($type, null, $options);
            } else {
                $child = $this->config->getBlockFactory()->createNamed($child, $type, null, $options);
            }
        }

        $child->setParent($this);
        $child->getConfig()->getType()->addParent($this, $child, $child->getOptions());
        $this->getConfig()->getType()->addChild($child, $this, $this->getOptions());

        if ($child->getParent() === $this) {
            $this->children[$child->getName()] = $child;
        }

        if (!$this->lockSetData && $this->defaultDataSet && !$this->config->getInheritData()) {
            var_dump('add child');
            $childrenIterator = new InheritDataAwareIterator(new \ArrayIterator(array($child)));
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
            $child = $this->children[$name];

            unset($this->children[$name]);

            $this->getConfig()->getType()->removeChild($child, $this, $this->getOptions());
            $child->getConfig()->getType()->removeParent($this, $child, $child->getOptions());
            $child->setParent(null);
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
        return $this->children;
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
        $options = $this->getOptions();

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
