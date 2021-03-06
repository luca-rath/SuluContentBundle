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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Application\ContentManager;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Content\Application\ContentCopier\ContentCopierInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentManager\ContentManager;
use Sulu\Bundle\ContentBundle\Content\Application\ContentManager\ContentManagerInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentPersister\ContentPersisterInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentProjectionNormalizer\ContentProjectionNormalizerInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentResolver\ContentResolverInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentWorkflow\ContentWorkflowInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentProjectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;

class ContentManagerTest extends TestCase
{
    protected function createContentManagerInstance(
        ContentResolverInterface $contentResolver,
        ContentPersisterInterface $contentPersister,
        ContentProjectionNormalizerInterface $contentProjectionNormalizer,
        ContentCopierInterface $contentCopier,
        ContentWorkflowInterface $contentWorkflow
    ): ContentManagerInterface {
        return new ContentManager($contentResolver, $contentPersister, $contentProjectionNormalizer, $contentCopier, $contentWorkflow);
    }

    public function testLoad(): void
    {
        $contentProjection = $this->prophesize(ContentProjectionInterface::class);
        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $dimensionAttributes = ['locale' => 'de', 'stage' => 'draft'];

        $contentResolver = $this->prophesize(ContentResolverInterface::class);
        $contentPersister = $this->prophesize(ContentPersisterInterface::class);
        $contentProjectionNormalizer = $this->prophesize(ContentProjectionNormalizerInterface::class);
        $contentCopier = $this->prophesize(ContentCopierInterface::class);
        $contentWorkflow = $this->prophesize(ContentWorkflowInterface::class);

        $contentManager = $this->createContentManagerInstance(
            $contentResolver->reveal(),
            $contentPersister->reveal(),
            $contentProjectionNormalizer->reveal(),
            $contentCopier->reveal(),
            $contentWorkflow->reveal()
        );

        $contentResolver->resolve($contentRichEntity->reveal(), $dimensionAttributes)
            ->willReturn($contentProjection->reveal())
            ->shouldBeCalled();

        $this->assertSame(
            $contentProjection->reveal(),
            $contentManager->resolve($contentRichEntity->reveal(), $dimensionAttributes)
        );
    }

    public function testPersist(): void
    {
        $contentProjection = $this->prophesize(ContentProjectionInterface::class);
        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $data = ['data' => 'value'];
        $dimensionAttributes = ['locale' => 'de', 'stage' => 'draft'];

        $contentResolver = $this->prophesize(ContentResolverInterface::class);
        $contentPersister = $this->prophesize(ContentPersisterInterface::class);
        $contentProjectionNormalizer = $this->prophesize(ContentProjectionNormalizerInterface::class);
        $contentCopier = $this->prophesize(ContentCopierInterface::class);
        $contentWorkflow = $this->prophesize(ContentWorkflowInterface::class);

        $contentManager = $this->createContentManagerInstance(
            $contentResolver->reveal(),
            $contentPersister->reveal(),
            $contentProjectionNormalizer->reveal(),
            $contentCopier->reveal(),
            $contentWorkflow->reveal()
        );

        $contentPersister->persist($contentRichEntity->reveal(), $data, $dimensionAttributes)
            ->willReturn($contentProjection->reveal())
            ->shouldBeCalled();

        $this->assertSame(
            $contentProjection->reveal(),
            $contentManager->persist($contentRichEntity->reveal(), $data, $dimensionAttributes)
        );
    }

    public function testResolve(): void
    {
        $contentProjection = $this->prophesize(ContentProjectionInterface::class);

        $contentResolver = $this->prophesize(ContentResolverInterface::class);
        $contentPersister = $this->prophesize(ContentPersisterInterface::class);
        $contentProjectionNormalizer = $this->prophesize(ContentProjectionNormalizerInterface::class);
        $contentCopier = $this->prophesize(ContentCopierInterface::class);
        $contentWorkflow = $this->prophesize(ContentWorkflowInterface::class);

        $contentManager = $this->createContentManagerInstance(
            $contentResolver->reveal(),
            $contentPersister->reveal(),
            $contentProjectionNormalizer->reveal(),
            $contentCopier->reveal(),
            $contentWorkflow->reveal()
        );

        $contentProjectionNormalizer->normalize($contentProjection->reveal())
            ->willReturn(['resolved' => 'data'])
            ->shouldBeCalled();

        $this->assertSame(
            ['resolved' => 'data'],
            $contentManager->normalize($contentProjection->reveal())
        );
    }

    public function testCopy(): void
    {
        $contentProjection = $this->prophesize(ContentProjectionInterface::class);

        $sourceContentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $sourceDimensionAttributes = ['locale' => 'en'];
        $targetContentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $targetDimensionAttributes = ['locale' => 'de'];

        $contentResolver = $this->prophesize(ContentResolverInterface::class);
        $contentPersister = $this->prophesize(ContentPersisterInterface::class);
        $contentProjectionNormalizer = $this->prophesize(ContentProjectionNormalizerInterface::class);
        $contentCopier = $this->prophesize(ContentCopierInterface::class);
        $contentWorkflow = $this->prophesize(ContentWorkflowInterface::class);

        $contentManager = $this->createContentManagerInstance(
            $contentResolver->reveal(),
            $contentPersister->reveal(),
            $contentProjectionNormalizer->reveal(),
            $contentCopier->reveal(),
            $contentWorkflow->reveal()
        );

        $contentCopier->copy(
            $sourceContentRichEntity->reveal(),
            $sourceDimensionAttributes,
            $targetContentRichEntity->reveal(),
            $targetDimensionAttributes
        )
            ->willReturn($contentProjection->reveal())
            ->shouldBeCalled();

        $this->assertSame(
            $contentProjection->reveal(),
            $contentManager->copy(
                $sourceContentRichEntity->reveal(),
                $sourceDimensionAttributes,
                $targetContentRichEntity->reveal(),
                $targetDimensionAttributes
            )
        );
    }

    public function testTransition(): void
    {
        $contentProjection = $this->prophesize(ContentProjectionInterface::class);

        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $dimensionAttributes = ['locale' => 'en'];
        $transitionName = 'review';

        $contentResolver = $this->prophesize(ContentResolverInterface::class);
        $contentPersister = $this->prophesize(ContentPersisterInterface::class);
        $contentProjectionNormalizer = $this->prophesize(ContentProjectionNormalizerInterface::class);
        $contentCopier = $this->prophesize(ContentCopierInterface::class);
        $contentWorkflow = $this->prophesize(ContentWorkflowInterface::class);

        $contentManager = $this->createContentManagerInstance(
            $contentResolver->reveal(),
            $contentPersister->reveal(),
            $contentProjectionNormalizer->reveal(),
            $contentCopier->reveal(),
            $contentWorkflow->reveal()
        );

        $contentWorkflow->apply(
            $contentRichEntity->reveal(),
            $dimensionAttributes,
            $transitionName
        )
            ->willReturn($contentProjection->reveal())
            ->shouldBeCalled();

        $this->assertSame(
            $contentProjection->reveal(),
            $contentManager->applyTransition(
                $contentRichEntity->reveal(),
                $dimensionAttributes,
                $transitionName
            )
        );
    }
}
