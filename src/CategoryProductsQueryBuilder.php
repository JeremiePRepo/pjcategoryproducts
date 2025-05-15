<?php

namespace JeremieP\PJCategoryProducts;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use PrestaShop\PrestaShop\Adapter\Configuration;
use PrestaShop\PrestaShop\Adapter\Shop\Repository\ShopGroupRepository;
use PrestaShop\PrestaShop\Core\Grid\Query\DoctrineSearchCriteriaApplicatorInterface;
use PrestaShop\PrestaShop\Core\Grid\Query\Filter\DoctrineFilterApplicatorInterface;
use PrestaShop\PrestaShop\Core\Grid\Query\ProductQueryBuilder;
use PrestaShop\PrestaShop\Core\Grid\Search\SearchCriteriaInterface;

class CategoryProductsQueryBuilder extends ProductQueryBuilder
{
    public function __construct(
        Connection $connection,
        string $dbPrefix,
        DoctrineSearchCriteriaApplicatorInterface $searchCriteriaApplicator,
        int $contextLanguageId,
        DoctrineFilterApplicatorInterface $filterApplicator,
        Configuration $configuration,
        ShopGroupRepository $shopGroupRepository
    ) {
        parent::__construct(
            $connection,
            $dbPrefix,
            $searchCriteriaApplicator,
            $contextLanguageId,
            $filterApplicator,
            $configuration,
            $shopGroupRepository
        );
    }

    public function getQueryBuilder(SearchCriteriaInterface $searchCriteria): QueryBuilder
    {
        if (!$searchCriteria instanceof \PrestaShop\PrestaShop\Core\Grid\Search\ShopSearchCriteriaInterface) {
            throw new \InvalidArgumentException('Invalid search criteria, expected a ShopSearchCriteriaInterface.');
        }

        $qb = parent::getSearchQueryBuilder($searchCriteria);

        // Ajout d'un filtre spécifique pour la catégorie
        $qb->andWhere('ps.id_category_default = :categoryId')
            ->setParameter('categoryId', (int) $searchCriteria->getFilters()['id_category']);

        return $qb;
    }
}