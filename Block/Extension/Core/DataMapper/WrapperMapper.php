<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Block\Extension\Core\DataMapper;

use Sonatra\Bundle\BlockBundle\Block\DataMapperInterface;

/**
 * Lets have a compound block with a data mapper not doing any work by default.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class WrapperMapper implements DataMapperInterface
{
    /**
     * {@inheritdoc}
     */
    public function mapDataToViews($data, $blocks)
    {
        if (!is_array($data) && !is_object($data)) {
            return;
        }

        foreach ($blocks as $block) {
            /* @var BlockInterface $block */
            $config = $block->getConfig();

            if ($config->getMapped()) {
                if (is_object($data)) {
                    $block->setDataClass(get_class($data));
                }

                $block->setData($data);
            }
        }
    }
}
