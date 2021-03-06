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

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentWorkflow\Subscriber;

use Sulu\Bundle\ContentBundle\Content\Application\ContentCopier\ContentCopierInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\TransitionEvent;

class ContentPublishSubscriber implements EventSubscriberInterface
{
    /**
     * @var ContentCopierInterface
     */
    private $contentCopier;

    public function __construct(ContentCopierInterface $contentCopier)
    {
        $this->contentCopier = $contentCopier;
    }

    public function onPublish(TransitionEvent $transitionEvent): void
    {
        $dimensionContent = $transitionEvent->getSubject();

        if (!$dimensionContent instanceof DimensionContentInterface) {
            return;
        }

        $context = $transitionEvent->getContext();

        $dimensionContentCollection = $context['dimensionContentCollection'] ?? null;
        $dimensionAttributes = $context['dimensionAttributes'] ?? null;
        $contentRichEntity = $context['contentRichEntity'] ?? null;

        if (!$dimensionAttributes) {
            throw new \RuntimeException('No "dimensionAttributes" given.');
        }

        if (!$dimensionContentCollection instanceof DimensionContentCollectionInterface) {
            throw new \RuntimeException('No "dimensionContentCollection" given.');
        }

        if (!$contentRichEntity instanceof ContentRichEntityInterface) {
            throw new \RuntimeException('No "contentRichEntity" given.');
        }

        $dimensionAttributes['stage'] = DimensionInterface::STAGE_LIVE;

        $this->contentCopier->copyFromDimensionContentCollection(
            $dimensionContentCollection,
            $contentRichEntity,
            $dimensionAttributes
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.content_workflow.transition.publish' => 'onPublish',
        ];
    }
}
