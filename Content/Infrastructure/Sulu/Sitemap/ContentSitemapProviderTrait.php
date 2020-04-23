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

namespace Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Sitemap;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;
use Sulu\Bundle\RouteBundle\Model\RouteInterface;
use Sulu\Bundle\WebsiteBundle\Sitemap\Sitemap;
use Sulu\Bundle\WebsiteBundle\Sitemap\SitemapAlternateLink;
use Sulu\Bundle\WebsiteBundle\Sitemap\SitemapProviderInterface;
use Sulu\Bundle\WebsiteBundle\Sitemap\SitemapUrl;
use Sulu\Component\Localization\Localization;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Component\Webspace\PortalInformation;

trait ContentSitemapProviderTrait
{
    public function build($page, $scheme, $host): array
    {
        $limit = $this->getPageSize();
        $offset = (int) (($page - 1) * $limit);

        $portalInformations = $this->getWebspaceManager()->findPortalInformationsByHostIncludingSubdomains(
            $host, $this->getKernelEnvironment()
        );

        /** @var PortalInformation|null $portalInformation */
        $portalInformation = array_shift($portalInformations);

        if (!$portalInformation) {
            return [];
        }

        $webspace = $portalInformation->getWebspace();
        $defaultLocale = $webspace->getDefaultLocalization()->getLocale(Localization::DASH);

        $routes = $this->getRoutes($limit, $offset);
        $groupedRoutes = $this->getGroupedRoutes($routes);

        $result = [];

        foreach ($groupedRoutes as $entityId => $routes) {
            $mainRoute = null;

            if (\array_key_exists($defaultLocale, $routes)) {
                $mainRoute = $routes[$defaultLocale];
                unset($routes[$defaultLocale]);
            } else {
                $mainRoute = array_shift($routes);
            }

            $sitemapUrl = $this->generateSitemapUrl(
                $mainRoute,
                $routes,
                $webspace->getKey(),
                $host,
                $scheme
            );

            if (null === $sitemapUrl) {
                continue;
            }

            $result[] = $sitemapUrl;
        }

        return $result;
    }

    public function createSitemap($scheme, $host): Sitemap
    {
        return new Sitemap(
            $this->getAlias(),
            $this->getMaxPage($scheme, $host)
        );
    }

    public function getMaxPage($scheme, $host): int
    {
        $queryBuilder = $this->createQueryBuilder('r');
        try {
            $amount = $queryBuilder
                ->select('COUNT(r.id)')
                ->getQuery()
                ->getSingleScalarResult();

            return (int) ceil($amount / $this->getPageSize());
        } catch (NoResultException | NonUniqueResultException $e) {
            return 0;
        }
    }

    /**
     * @param RouteInterface[] $routes
     *
     * @return mixed[]
     */
    protected function getGroupedRoutes(array $routes): array
    {
        $result = [];

        foreach ($routes as $route) {
            $entityId = $route->getEntityId();

            if (!\array_key_exists($entityId, $result)) {
                $result[$entityId] = [];
            }

            $result[$entityId][$route->getLocale()] = $route;
        }

        return $result;
    }

    /**
     * @return mixed[]
     */
    protected function getRoutes(int $limit, int $offset): array
    {
        $queryBuilder = $this->createQueryBuilder('r');

        return $queryBuilder
            ->select('r')
            ->distinct()
            ->orderBy('r.entityId', 'asc')
            ->getQuery()
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getResult();
    }

    protected function createQueryBuilder(string $alias): QueryBuilder
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        return $queryBuilder
            ->from($this->getEntityClass(), 'e')
            ->innerJoin('e.dimensionContents', 'dc')
            ->innerJoin('dc.dimension', 'd')
            ->innerJoin($this->getRouteClass(), $alias, Join::WITH, $alias . '.entityId = e.id')
            ->where('d.stage = :stage')
            ->andWhere($alias . '.entityClass = :entityClass')
            ->andWhere($alias . '.history = :history')
            ->andWhere($alias . '.locale = d.locale')
            ->setParameters([
                'stage' => DimensionInterface::STAGE_LIVE,
                'entityClass' => $this->getEntityClass(),
                'history' => false,
            ]);
    }

    /**
     * @param RouteInterface[] $alternateRoutes
     */
    protected function generateSitemapUrl(
        RouteInterface $route,
        array $alternateRoutes,
        string $webspaceKey,
        string $host,
        string $scheme
    ): ?SitemapUrl {
        $url = $this->generateUrl($route, $webspaceKey, $host, $scheme);

        if (!$url) {
            return null;
        }

        $sitemapUrl = new SitemapUrl(
            $url,
            $route->getLocale(),
            $route->getLocale()
        );

        foreach ($alternateRoutes as $alternateRoute) {
            $alternateUrl = $this->generateUrl($alternateRoute, $webspaceKey, $host, $scheme);

            if (!$alternateUrl) {
                continue;
            }

            $alternateLink = new SitemapAlternateLink($alternateUrl, $alternateRoute->getLocale());

            $sitemapUrl->addAlternateLink($alternateLink);
        }

        return $sitemapUrl;
    }

    protected function generateUrl(
        RouteInterface $route,
        string $webspaceKey,
        string $host,
        string $scheme
    ): ?string {
        $url = $this->getWebspaceManager()->findUrlByResourceLocator(
            $route->getPath(),
            $this->getKernelEnvironment(),
            $route->getLocale(),
            $webspaceKey,
            $host,
            $scheme
        );

        return $url ?: null;
    }

    protected function getPageSize(): int
    {
        return SitemapProviderInterface::PAGE_SIZE;
    }

    abstract protected function getEntityManager(): EntityManagerInterface;

    abstract protected function getWebspaceManager(): WebspaceManagerInterface;

    abstract protected function getKernelEnvironment(): string;

    abstract protected function getEntityClass(): string;

    abstract protected function getRouteClass(): string;

    abstract public function getAlias(): string;
}