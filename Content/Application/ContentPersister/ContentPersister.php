<?php

declare(strict_types=1);

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentPersister;

use Sulu\Bundle\ContentBundle\Content\Domain\Factory\ContentProjectionFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\DimensionCollectionFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\DimensionContentCollectionFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentProjectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;

class ContentPersister implements ContentPersisterInterface
{
    /**
     * @var DimensionCollectionFactoryInterface
     */
    private $dimensionCollectionFactory;

    /**
     * @var DimensionContentCollectionFactoryInterface
     */
    private $dimensionContentCollectionFactory;

    /**
     * @var ContentProjectionFactoryInterface
     */
    private $viewFactory;

    public function __construct(
        DimensionCollectionFactoryInterface $dimensionCollectionFactory,
        DimensionContentCollectionFactoryInterface $dimensionContentCollectionFactory,
        ContentProjectionFactoryInterface $viewFactory
    ) {
        $this->dimensionCollectionFactory = $dimensionCollectionFactory;
        $this->dimensionContentCollectionFactory = $dimensionContentCollectionFactory;
        $this->viewFactory = $viewFactory;
    }

    public function persist(ContentRichEntityInterface $contentRichEntity, array $data, array $dimensionAttributes): ContentProjectionInterface
    {
        $dimensionCollection = $this->dimensionCollectionFactory->create($dimensionAttributes);
        $dimensionContentCollection = $this->dimensionContentCollectionFactory->create(
            $contentRichEntity,
            $dimensionCollection,
            $data
        );

        return $this->viewFactory->create($dimensionContentCollection);
    }
}
