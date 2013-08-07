<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Doctrine\Block;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\ORM\Mapping\MappingException as LegacyMappingException;
use Sonatra\Bundle\BlockBundle\Block\BlockTypeGuesserInterface;
use Sonatra\Bundle\BlockBundle\Block\Guess\Guess;
use Sonatra\Bundle\BlockBundle\Block\Guess\TypeGuess;

class DoctrineOrmTypeGuesser implements BlockTypeGuesserInterface
{
    protected $registry;
    private $cache;

    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
        $this->cache = array();
    }

    /**
     * {@inheritdoc}
     */
    public function guessType($class, $property)
    {
        if (!$ret = $this->getMetadata($class)) {
            return new TypeGuess('text', array(), Guess::LOW_CONFIDENCE);
        }

        list($metadata, $name) = $ret;

        if ($metadata->hasAssociation($property)) {
            $multiple = $metadata->isCollectionValuedAssociation($property);
            $mapping = $metadata->getAssociationMapping($property);

            return new TypeGuess('entity', array('em' => $name, 'class' => $mapping['targetEntity'], 'multiple' => $multiple), Guess::HIGH_CONFIDENCE);
        }

        switch ($metadata->getTypeOfField($property)) {
            case 'array':
                return new TypeGuess('collection', array(), Guess::MEDIUM_CONFIDENCE);

            case 'boolean':
                return new TypeGuess('checkbox', array(), Guess::HIGH_CONFIDENCE);

            case 'datetime':
            case 'vardatetime':
            case 'datetimetz':
                return new TypeGuess('datetime', array(), Guess::HIGH_CONFIDENCE);

            case 'date':
                return new TypeGuess('date', array(), Guess::HIGH_CONFIDENCE);

            case 'time':
                return new TypeGuess('time', array(), Guess::HIGH_CONFIDENCE);

            case 'decimal':
            case 'float':
                return new TypeGuess('number', array(), Guess::MEDIUM_CONFIDENCE);

            case 'integer':
            case 'bigint':
            case 'smallint':
                return new TypeGuess('integer', array(), Guess::MEDIUM_CONFIDENCE);

            case 'string':
                return new TypeGuess('text', array(), Guess::MEDIUM_CONFIDENCE);

            case 'text':
                return new TypeGuess('textarea', array(), Guess::MEDIUM_CONFIDENCE);

            default:
                return new TypeGuess('text', array(), Guess::LOW_CONFIDENCE);
        }
    }

    /**
     * Get class metadata.
     *
     * @param string $class
     *
     * @return ClassMetadata
     */
    protected function getMetadata($class)
    {
        if (array_key_exists($class, $this->cache)) {
            return $this->cache[$class];
        }

        $this->cache[$class] = null;

        foreach ($this->registry->getManagers() as $name => $em) {
            try {
                return $this->cache[$class] = array($em->getClassMetadata($class), $name);

            } catch (MappingException $e) {
                // not an entity or mapped super class
            } catch (LegacyMappingException $e) {
                // not an entity or mapped super class, using Doctrine ORM 2.2
            }
        }
    }
}
