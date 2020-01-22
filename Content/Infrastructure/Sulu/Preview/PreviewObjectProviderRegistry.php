<?php

declare(strict_types=1);

namespace Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Preview;

use Sulu\Bundle\PreviewBundle\Preview\Object\PreviewObjectProviderInterface;

class PreviewObjectProviderRegistry
{
    /**
     * @var PreviewObjectProviderInterface[]
     */
    private $objectProviders;

    /**
     * @param PreviewObjectProviderInterface[] $objectProviders
     */
    public function __construct(array $objectProviders)
    {
        $this->objectProviders = $objectProviders;
    }

    /**
     * @return PreviewObjectProviderInterface[]
     */
    public function getObjectProviders(): array
    {
        return $this->objectProviders;
    }

    public function hasObjectProvider(string $providerKey): bool
    {
        return array_key_exists($providerKey, $this->objectProviders);
    }
}
