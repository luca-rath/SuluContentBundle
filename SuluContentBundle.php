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

namespace Sulu\Bundle\ContentBundle;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;
use Sulu\Bundle\PersistenceBundle\PersistenceBundleTrait;
use Sulu\Component\Symfony\CompilerPass\TaggedServiceCollectorCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SuluContentBundle extends Bundle
{
    use PersistenceBundleTrait;

    public function build(ContainerBuilder $container)
    {
        $this->buildPersistence(
            [
                DimensionInterface::class => 'sulu.model.dimension.class',
            ],
            $container
        );

        $container->addCompilerPass(
            new TaggedServiceCollectorCompilerPass(
                'sulu_content.preview_object_provider_registry',
                'sulu_preview.object_provider',
                0,
                'provider-key'
            )
        );
    }
}
