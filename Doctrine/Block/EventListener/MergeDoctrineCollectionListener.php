<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Doctrine\Block\EventListener;

use Sonatra\Bundle\BlockBundle\Block\BlockEvents;
use Sonatra\Bundle\BlockBundle\Block\BlockEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Merge changes from the request to a Doctrine\Common\Collections\Collection instance.
 *
 * This works with ORM, MongoDB and CouchDB instances of the collection interface.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 *
 * @see    Doctrine\Common\Collections\Collection
 */
class MergeDoctrineCollectionListener implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        // Higher priority than core MergeCollectionListener so that this one
        // is called before
        return array(BlockEvents::BIND => array('onBind', 10));
    }

    /**
     * On bind.
     *
     * @param BlockEvent $event
     */
    public function onBind(BlockEvent $event)
    {
        $collection = $event->getBlock()->getData();
        $data = $event->getData();

        // If all items were removed, call clear which has a higher
        // performance on persistent collections
        if ($collection && count($data) === 0) {
            $collection->clear();
        }
    }
}
