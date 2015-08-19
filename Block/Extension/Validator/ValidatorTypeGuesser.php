<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Block\Extension\Validator;

use Sonatra\Bundle\BlockBundle\Block\BlockTypeGuesserInterface;
use Sonatra\Bundle\BlockBundle\Block\Guess\Guess;
use Sonatra\Bundle\BlockBundle\Block\Guess\TypeGuess;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Mapping\Factory\MetadataFactoryInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ValidatorTypeGuesser implements BlockTypeGuesserInterface
{
    protected $metadataFactory;

    /**
     * Constructor.
     *
     * @param MetadataFactoryInterface $metadataFactory
     */
    public function __construct(MetadataFactoryInterface $metadataFactory)
    {
        $this->metadataFactory = $metadataFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function guessType($class, $property)
    {
        $guesser = $this;

        return $this->guess($class, $property, function (Constraint $constraint) use ($guesser) {
            return $guesser->guessTypeForConstraint($constraint);
        });
    }

    /**
     * Guesses a field class name for a given constraint
     *
     * @param Constraint $constraint The constraint to guess for
     *
     * @return TypeGuess The guessed field class and options
     */
    public function guessTypeForConstraint(Constraint $constraint)
    {
        $empty = null;

        switch (get_class($constraint)) {
            case 'Symfony\Component\Validator\Constraints\Type':
                /* @var \Symfony\Component\Validator\Constraints\Type $constraint */
                switch ($constraint->type) {
                    case 'array':
                        return new TypeGuess('collection', array(), Guess::MEDIUM_CONFIDENCE);
                    case 'boolean':
                    case 'bool':
                        return new TypeGuess('checkbox', array(), Guess::MEDIUM_CONFIDENCE);

                    case 'double':
                    case 'float':
                    case 'numeric':
                    case 'real':
                        return new TypeGuess('number', array(), Guess::MEDIUM_CONFIDENCE);

                    case 'integer':
                    case 'int':
                    case 'long':
                        return new TypeGuess('integer', array(), Guess::MEDIUM_CONFIDENCE);

                    case '\DateTime':
                        return new TypeGuess('date', array(), Guess::MEDIUM_CONFIDENCE);

                    case 'string':
                        return new TypeGuess('text', array(), Guess::LOW_CONFIDENCE);
                }
                break;

            case 'Symfony\Component\Validator\Constraints\Country':
                return new TypeGuess('country', array(), Guess::HIGH_CONFIDENCE);

            case 'Symfony\Component\Validator\Constraints\Date':
                return new TypeGuess('date', array('input' => 'string'), Guess::HIGH_CONFIDENCE);

            case 'Symfony\Component\Validator\Constraints\DateTime':
                return new TypeGuess('datetime', array('input' => 'string'), Guess::HIGH_CONFIDENCE);

            case 'Symfony\Component\Validator\Constraints\Email':
                return new TypeGuess('email', array(), Guess::HIGH_CONFIDENCE);

            case 'Symfony\Component\Validator\Constraints\File':
            case 'Symfony\Component\Validator\Constraints\Image':
                return new TypeGuess('file', array(), Guess::HIGH_CONFIDENCE);

            case 'Symfony\Component\Validator\Constraints\Language':
                return new TypeGuess('language', array(), Guess::HIGH_CONFIDENCE);

            case 'Symfony\Component\Validator\Constraints\Locale':
                return new TypeGuess('locale', array(), Guess::HIGH_CONFIDENCE);

            case 'Symfony\Component\Validator\Constraints\Time':
                return new TypeGuess('time', array('input' => 'string'), Guess::HIGH_CONFIDENCE);

            case 'Symfony\Component\Validator\Constraints\Url':
                return new TypeGuess('url', array(), Guess::HIGH_CONFIDENCE);

            case 'Symfony\Component\Validator\Constraints\Ip':
                return new TypeGuess('text', array(), Guess::MEDIUM_CONFIDENCE);

            case 'Symfony\Component\Validator\Constraints\Length':
            case 'Symfony\Component\Validator\Constraints\Regex':
                return new TypeGuess('text', array(), Guess::LOW_CONFIDENCE);

            case 'Symfony\Component\Validator\Constraints\Range':
                return new TypeGuess('number', array(), Guess::LOW_CONFIDENCE);

            case 'Symfony\Component\Validator\Constraints\Count':
                return new TypeGuess('collection', array(), Guess::LOW_CONFIDENCE);

            case 'Symfony\Component\Validator\Constraints\IsTrue':
            case 'Symfony\Component\Validator\Constraints\IsFalse':
                return new TypeGuess('checkbox', array(), Guess::MEDIUM_CONFIDENCE);
        }

        return $empty;
    }

    /**
     * Iterates over the constraints of a property, executes a constraints on
     * them and returns the best guess
     *
     * @param string   $class    The class to read the constraints block
     * @param string   $property The property for which to find constraints
     * @param \Closure $closure  The closure that returns a guess for a given constraint
     *
     * @return Guess The guessed value with the highest confidence
     */
    protected function guess($class, $property, \Closure $closure)
    {
        $guesses = array();
        /* @var ClassMetadata $classMetadata */
        $classMetadata = $this->metadataFactory->getMetadataFor($class);

        if ($classMetadata->hasPropertyMetadata($property)) {
            $memberMetadatas = $classMetadata->getPropertyMetadata($property);

            foreach ($memberMetadatas as $memberMetadata) {
                $constraints = $memberMetadata->getConstraints();

                foreach ($constraints as $constraint) {
                    if ($guess = $closure($constraint)) {
                        $guesses[] = $guess;
                    }
                }
            }
        }

        return Guess::getBestGuess($guesses);
    }
}
