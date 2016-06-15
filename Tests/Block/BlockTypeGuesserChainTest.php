<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Tests\Block;

use Sonatra\Bundle\BlockBundle\Block\BlockTypeGuesserChain;
use Sonatra\Bundle\BlockBundle\Block\Guess\Guess;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class BlockTypeGuesserChainTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Sonatra\Bundle\BlockBundle\Block\Exception\UnexpectedTypeException
     */
    public function testInvalidGuessers()
    {
        new BlockTypeGuesserChain(array(42));
    }

    public function testGuessers()
    {
        $guessers = new BlockTypeGuesserChain(array(
            $this->getMockBuilder('Sonatra\Bundle\BlockBundle\Block\BlockTypeGuesserInterface')->getMock(),
            new BlockTypeGuesserChain(array(
                $this->getMockBuilder('Sonatra\Bundle\BlockBundle\Block\BlockTypeGuesserInterface')->getMock(),
            )),
        ));

        $ref = new \ReflectionClass($guessers);
        $ref = $ref->getProperty('guessers');
        $ref->setAccessible(true);
        $value = $ref->getValue($guessers);

        $this->assertEquals(array(
            $this->getMockBuilder('Sonatra\Bundle\BlockBundle\Block\BlockTypeGuesserInterface')->getMock(),
            $this->getMockBuilder('Sonatra\Bundle\BlockBundle\Block\BlockTypeGuesserInterface')->getMock(),
        ), $value);
    }

    public function testGuessType()
    {
        $guess = $this->getMockForAbstractClass('Sonatra\Bundle\BlockBundle\Block\Guess\Guess', array(Guess::MEDIUM_CONFIDENCE));
        $guesser = $this->getMockBuilder('Sonatra\Bundle\BlockBundle\Block\BlockTypeGuesserInterface')->getMock();
        $guessers = new BlockTypeGuesserChain(array($guesser));

        $guesser->expects($this->any())
            ->method('guessType')
            ->will($this->returnValue($guess));

        $this->assertEquals($guess, $guessers->guessType('stdClass', 'bar'));
    }
}
