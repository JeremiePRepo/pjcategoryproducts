<?php

namespace JeremieP\PJCategoryProducts;

use PrestaShop\PrestaShop\Core\Grid\Data\Factory\GridDataFactoryInterface;
use PrestaShop\PrestaShop\Core\Localization\Locale\Repository;
use PrestaShop\PrestaShop\Core\Grid\Data\GridData;
use PrestaShop\PrestaShop\Core\Grid\Search\SearchCriteriaInterface;

class CategoryProductsGridDataFactory implements GridDataFactoryInterface
{
    private $decoratedFactory;
    private $localeRepository;

    public function __construct(
        GridDataFactoryInterface $decoratedFactory,
        Repository $localeRepository
    ) {
        $this->decoratedFactory = $decoratedFactory;
        $this->localeRepository = $localeRepository;
    }

    public function getData(SearchCriteriaInterface $searchCriteria): GridData
    {
        $gridData = $this->decoratedFactory->getData($searchCriteria);

        // Modifiez les données si nécessaire
        return $gridData;
    }
}